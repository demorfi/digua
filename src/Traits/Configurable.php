<?php declare(strict_types=1);

namespace Digua\Traits;

trait Configurable
{
    /**
     * @var array
     */
    protected static array $config = [];

    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    public static function setConfigValue(string $name, mixed $value): void
    {
        static::$config[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getConfigValue(string $name): mixed
    {
        return static::$config[$name] ?? static::$defaults[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasConfigValue(string $name): bool
    {
        return isset(static::$config[$name]) || isset(static::$defaults[$name]);
    }
}