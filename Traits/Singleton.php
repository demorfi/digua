<?php

namespace Digua\Traits;

use Digua\Exceptions\Singleton as SingletonException;

trait Singleton
{
    /**
     * Instance.
     *
     * @var array
     */
    protected static array $instance = [];

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }

    /**
     * Disabled cloning.
     *
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
     * Fake constructor.
     */
    protected function __init(): void
    {
    }

    /**
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        return call_user_func_array([self::$instance[get_called_class()], $method], $args);
    }

    /**
     * Get instance.
     *
     * @param string|null $name
     * @return static
     */
    public static function getInstance(string $name = null): static
    {
        if (isset(self::$instance[$name])) {
            return self::$instance[$name];
        }

        $called = get_called_class();
        if (!isset(self::$instance[$called])) {
            self::$instance[$called] = new static();
            self::$instance[$called]->__init();
        }

        return self::$instance[$called];
    }

    /**
     * New instance.
     *
     * @return static
     */
    public static function newInstance(): static
    {
        $called = get_called_class();
        if (!isset(self::$instance[$called])) {
            self::$instance[$called] = new static();
            self::$instance[$called]->__init();
        }

        return self::$instance[$called];
    }
}
