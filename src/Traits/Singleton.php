<?php declare(strict_types=1);

namespace Digua\Traits;

use BadMethodCallException;
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
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    final public static function __callStatic(string $name, array $arguments): mixed
    {
        $name = preg_replace('/^static/', '', $name);
        if (!method_exists(self::getInstance(), $name)) {
            throw new BadMethodCallException('method ' . $name . ' does not exist!');
        }

        return self::getInstance()->$name(...$arguments);
    }

    /**
     * @return static
     */
    final public static function getInstance(): static
    {
        return static::$instance ??= new static;
    }
}
