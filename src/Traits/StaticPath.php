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
     * @param string $fileName Allows only symbols A-Za-z0-9-_.
     * @return string
     */
    public static function getPathToFile(string $fileName): string
    {
        $fileName = preg_replace('/[^A-Za-z0-9\-_.]|\.{2,}/', '', $fileName);
        return self::$path . '/' . $fileName;
    }

    /**
     * @return bool
     */
    public static function isReadablePath(): bool
    {
        return is_readable(self::$path);
    }

    /**
     * @return bool
     * @throws PathException
     */
    public static function throwIsEmptyPath(): bool
    {
        return empty(self::$path)
            ? throw new PathException('The path for ' . self::class . ' is not configured!')
            : false;
    }
}