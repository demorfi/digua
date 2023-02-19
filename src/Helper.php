<?php declare(strict_types=1);

namespace Digua;

use Digua\Exceptions\Path as PathException;
use BadMethodCallException;

/**
 * @method static string filterFileName(string $fileName);
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
     * @return void
     */
    public static function addHelper(string $name, callable $callable): void
    {
        self::$helpers[$name] = $callable;
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
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $defName = 'default' . ucfirst($name);
        $helper  = self::$helpers[$name] ?? method_exists(self::class, $defName) ? $defName : null;

        if (empty($helper)) {
            throw new BadMethodCallException('helper method ' . $name . ' does not exist!');
        }

        return self::$helper(...$arguments);
    }
}