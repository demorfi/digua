<?php

namespace Digua\Traits;

use Digua\Exceptions\Path as PathException;

trait StaticPath
{
    /**
     * Path.
     *
     * @var string
     */
    public static string $path = '';

    /**
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        static::$path = $path;
    }

    /**
     * @return string
     */
    public static function getPath(): string
    {
        return self::$path;
    }

    /**
     * @return bool
     * @throws PathException
     */
    public static function isEmptyPath(): bool
    {
        return empty(static::$path)
            ? throw new PathException('The path for ' . self::class . ' is not configured!')
            : false;
    }
}