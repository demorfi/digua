<?php

namespace Digua\Traits;

use Digua\Exceptions\Singleton as SingletonException;

trait Singleton
{
    /**
     * Instances.
     *
     * @var static[]
     */
    protected static array $instance = [];

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
        throw new SingletonException('object unserialize forbidden');
    }

    /**
     * @throws SingletonException
     */
    public function __sleep()
    {
        throw new SingletonException('object serialize forbidden');
    }

    /**
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        return call_user_func_array([
            self::getInstance(),
            preg_replace('/^static/', '', $method)
        ], $args);
    }

    /**
     * Get instance.
     *
     * @param string|null $name
     * @return static
     */
    public static function getInstance(string $name = null): static
    {
        return self::$instance[$name] ?? self::newInstance();
    }

    /**
     * New instance.
     *
     * @return static
     */
    protected static function newInstance(): static
    {
        $called = get_called_class();
        self::$instance[$called] ??= new static();
        return self::$instance[$called];
    }
}
