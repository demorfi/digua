<?php declare(strict_types=1);

namespace Digua;

use Digua\Exceptions\{
    Path as PathException,
    BadMethodCall as BadMethodCallException
};
use ValueError;
use Exception;

/**
 * @method static string filterFileName(string $fileName);
 * @method static int makeIntHash();
 */
class Helper
{
    /**
     * @var array
     */
    private static array $helpers = [];

    /**
     * @param string   $name
     * @param callable $callable
     * @param bool     $force
     * @return void
     * @throws ValueError If helper already exists and the force argument is not set
     */
    public static function register(string $name, callable $callable, bool $force = false): void
    {
        if (!$force && isset(self::$helpers[$name])) {
            throw new ValueError(sprintf('Helper (%s) already exists!', $name));
        }

        self::$helpers[$name] = $callable;
    }

    /**
     * @param string $name
     * @return ?callable
     */
    public static function get(string $name): ?callable
    {
        return self::$helpers[$name] ?? null;
    }

    /**
     * @param string $fileName Allows only symbols A-Za-z/0-9-_.
     * @return string
     */
    protected static function defaultFilterFileName(string $fileName): string
    {
        return preg_replace(['/[^A-Za-z0-9\-_.\/]/', '/\.{2,}/', '/\/{2,}|\/$/', '/\.+\//'], '', $fileName);
    }

    /**
     * @return int
     * @throws Exception
     */
    public static function defaultMakeIntHash(): int
    {
        return random_int(time(), PHP_INT_MAX);
    }

    /**
     * @param string $name
     * @return Config
     * @throws PathException
     */
    public static function config(string $name): Config
    {
        return new Config($name);
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (isset(self::$helpers[$name])) {
            return self::$helpers[$name](...$arguments);
        }

        $defName = 'default' . ucfirst($name);
        if (method_exists(self::class, $defName)) {
            return self::$defName(...$arguments);
        }

        throw new BadMethodCallException(sprintf('Helper (%s) does not exist!', $name));
    }
}