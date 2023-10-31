<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Exceptions\{
    Singleton as SingletonException,
    BadMethodCall as BadMethodCallException
};

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
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    final public static function __callStatic(string $name, array $arguments): mixed
    {
        $name = lcfirst(preg_replace('/^static/', '', $name));
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
