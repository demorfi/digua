<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Exceptions\Path as PathException;

trait DiskPath
{
    use Configurable;

    /**
     * @param string $path
     * @return void
     */
    public static function setDiskPath(string $path): void
    {
        self::setConfigValue('diskPath', rtrim($path, '/'));
    }

    /**
     * @param string $append
     * @return string
     */
    public static function getDiskPath(string $append = ''): string
    {
        return self::getConfigValue('diskPath') . (!empty($append) ? '/' . trim($append, '/') : '');
    }

    /**
     * @param string $path
     * @return string
     */
    protected static function appendToDiskPath(string $path): string
    {
        $path = self::getConfigValue('diskPath') . '/' . trim($path, '/');
        self::setConfigValue('diskPath', $path);
        return $path;
    }

    /**
     * @return bool
     */
    public static function isReadableDiskPath(): bool
    {
        $path = self::getConfigValue('diskPath');
        return is_dir($path) && is_readable($path);
    }

    /**
     * @return bool
     */
    public static function isWritableDiskPath(): bool
    {
        $path = self::getConfigValue('diskPath');
        return is_dir($path) && is_writable($path);
    }

    /**
     * @return bool
     * @throws PathException
     */
    public static function throwIsBrokenDiskPath(): bool
    {
        if (!self::hasConfigValue('diskPath')) {
            throw new PathException(sprintf('The disk path for (%s) is not configured!', self::class), 100);
        }

        $path = self::getConfigValue('diskPath');
        if (!is_dir($path) || !is_readable($path)) {
            throw new PathException(sprintf('The disk path (%s) is not readable!', $path), 200);
        }

        return false;
    }
}