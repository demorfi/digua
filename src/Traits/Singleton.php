<?php declare(strict_types = 1);

namespace Digua\Traits;

use Digua\Exceptions\Singleton as SingletonException;

trait Singleton
{
    private static ?self $instance = null;

    private function __construct()
    {
    }

    /**
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * @throws SingletonException
     */
    public function __wakeup()
    {
        throw new SingletonException('Object unserialize forbidden!');
    }

    /**
     * @throws SingletonException
     */
    public function __sleep()
    {
        throw new SingletonException('Object serialize forbidden!');
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return call_user_func_array([
            static::getInstance(),
            preg_replace('/^static/', '', $method)
        ], $arguments);
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return static::$instance === null
            ? static::$instance = new static()
            : static::$instance;
    }
}
