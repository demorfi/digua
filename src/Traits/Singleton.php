<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Exceptions\Singleton as SingletonException;

trait Singleton
{
    private static ?self $instance = null;

    protected function __construct()
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
    final public function __wakeup()
    {
        throw new SingletonException('Object unserialize forbidden!');
    }

    /**
     * @throws SingletonException
     */
    final public function __sleep()
    {
        throw new SingletonException('Object serialize forbidden!');
    }

    /**
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    final public static function __callStatic(string $method, array $arguments): mixed
    {
        return call_user_func_array([
            self::getInstance(),
            preg_replace('/^static/', '', $method)
        ], $arguments);
    }

    /**
     * @return static
     */
    final public static function getInstance(): static
    {
        return static::$instance === null
            ? static::$instance = new static
            : static::$instance;
    }
}
