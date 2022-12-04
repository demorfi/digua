<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Exceptions\Path as PathException;

trait StaticPath
{
    /**
     * @var string
     */
    private static string $path = '';

    /**
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        self::$path = rtrim($path, '/');
    }

    /**
     * @return string
     */
    public static function getPath(): string
    {
        return self::$path;
    }

    /**
     * @param string $path
     * @return string Result path
     */
    protected static function appendToPath(string $path): string
    {
        self::$path .= '/' . rtrim($path, '/');
        return self::$path;
    }

    /**
     * @param string $fileName Allows only symbols A-Za-z/0-9-_.
     * @return string
     */
    protected static function getPathToFile(string $fileName): string
    {
        $fileName = preg_replace(['/[^A-Za-z0-9\-_.\/]/', '/\.{2,}/', '/\/{2,}|\/$/', '/\.+\//'], '', $fileName);
        return self::$path . '/' . $fileName;
    }

    /**
     * @return bool
     */
    public static function isReadablePath(): bool
    {
        return is_dir(self::$path) && is_readable(self::$path);
    }

    /**
     * @return bool
     */
    public static function isWritablePath(): bool
    {
        return is_dir(self::$path) && is_writable(self::$path);
    }

    /**
     * @return bool
     * @throws PathException
     */
    public static function throwIsBrokenPath(): bool
    {
        if (empty(self::$path)) {
            throw new PathException('The path for ' . self::class . ' is not configured!');
        }

        if (!self::isReadablePath()) {
            throw new PathException('The path for ' . self::class . ' is not readable!');
        }

        return false;
    }
}