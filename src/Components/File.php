<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Exceptions\File as FileException;

class File
{
    /**
     * @var mixed|false|resource
     */
    protected readonly mixed $handle;

    /**
     * @param string $filePath
     * @throws FileException
     */
    public function __construct(private readonly string $filePath)
    {
        if (is_dir($this->filePath) || !is_writable($this->filePath)) {
            throw new FileException(sprintf('File (%s) is not writable!', $this->filePath));
        }

        $this->handle = fopen($this->filePath, 'r+');
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    /**
     * @return bool
     * @throws FileException
     */
    public function readLock(): bool
    {
        if (!flock($this->handle, LOCK_SH)) {
            throw new FileException(sprintf('File (%s) cannot be locked!', $this->filePath));
        }
        return true;
    }

    /**
     * @return bool
     * @throws FileException
     */
    public function writeLock(): bool
    {
        if (!flock($this->handle, LOCK_EX)) {
            throw new FileException(sprintf('File (%s) cannot be locked!', $this->filePath));
        }
        return true;
    }

    /**
     * @return bool
     * @throws FileException
     */
    public function unlock(): bool
    {
        if (!flock($this->handle, LOCK_UN)) {
            throw new FileException(sprintf('File (%s) cannot be unlocked!', $this->filePath));
        }
        return true;
    }

    /**
     * @return int
     */
    public function filesize(): int
    {
        fseek($this->handle, 0, SEEK_END);
        return (int)ftell($this->handle);
    }

    /**
     * @param int $size
     * @return bool
     */
    public function truncate(int $size): bool
    {
        return ftruncate($this->handle, $size);
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return $this->truncate(0);
    }

    /**
     * @param int      $offset
     * @param int|null $length
     * @return string
     */
    public function readLeft(int $offset = 0, ?int $length = null): string
    {
        $filesize = $this->filesize();
        fseek($this->handle, abs($offset));
        return (string)fread($this->handle, $length ?: $filesize ?: 1);
    }

    /**
     * @param int      $offset
     * @param int|null $length
     * @return string
     */
    public function readRight(int $offset = 0, ?int $length = null): string
    {
        $filesize = $this->filesize();
        fseek($this->handle, -abs($offset) ?: -$length ?: -$filesize, SEEK_END);
        return (string)fread($this->handle, $length ?: $filesize ?: 1);
    }

    /**
     * @param string   $data
     * @param int      $offset
     * @param int|null $length
     * @return bool
     */
    public function writeLeft(string $data, int $offset = 0, ?int $length = null): bool
    {
        fseek($this->handle, abs($offset));
        return (bool)fwrite($this->handle, $data, $length);
    }

    /**
     * @param string   $data
     * @param int      $offset
     * @param int|null $length
     * @return bool
     */
    public function writeRight(string $data, int $offset = 0, ?int $length = null): bool
    {
        fseek($this->handle, -abs($offset), SEEK_END);
        return (bool)fwrite($this->handle, $data, $length);
    }
}