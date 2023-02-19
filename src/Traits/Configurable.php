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
        self::$config[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function getConfigValue(string $name): mixed
    {
        return self::$config[$name] ?? self::$defaults[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasConfigValue(string $name): bool
    {
        return isset(self::$config[$name]) || isset(self::$defaults[$name]);
    }
}