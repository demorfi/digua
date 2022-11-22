<?php

namespace Digua;

use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};
use Digua\Enums\ContentType;
use Digua\Traits\StaticPath;

class Storage
{
    use StaticPath;

    /**
     * Storage full file path.
     *
     * @var string
     */
    protected string $filePath;

    /**
     * Storage content.
     *
     * @var mixed
     */
    protected mixed $content;

    /**
     * @param string      $fileName    Storage file name
     * @param ContentType $contentType Storage type
     * @throws PathException
     * @throws StorageException
     */
    public function __construct(protected string $fileName, protected ContentType $contentType)
    {
        self::isEmptyPath();
        $this->filePath = static::$path . DIRECTORY_SEPARATOR . $this->fileName;

        if (!is_file($this->filePath)) {
            $this->create();
        }
    }

    /**
     * Read storage content.
     *
     * @return mixed
     * @throws StorageException
     */
    public function read(): mixed
    {
        if (!is_readable($this->filePath)) {
            throw new StorageException($this->filePath . ' - file is not readable!');
        }

        $this->content = match ($this->contentType) {
            ContentType::JSON => json_decode(file_get_contents($this->filePath), true),
            default => file_get_contents($this->filePath)
        };

        return $this->content;
    }

    /**
     * Set storage content.
     *
     * @param mixed $content
     * @return void
     * @throws StorageException
     */
    public function replace(mixed $content): void
    {
        $byType = match ($this->contentType) {
            ContentType::JSON => is_object($content) || is_array($content),
            default => is_string($content)
        };

        if (!$byType) {
            throw new StorageException($this->filePath . ' - type mismatch!');
        }

        $this->content = $content;
    }

    /**
     * Rewrite storage content.
     *
     * @return void
     * @throws StorageException
     */
    public function save(): void
    {
        if (!is_writable($this->filePath)) {
            throw new StorageException($this->filePath . ' - not writable!');
        }

        match ($this->contentType) {
            ContentType::JSON => file_put_contents($this->filePath, json_encode($this->content), LOCK_EX),
            default => file_put_contents($this->filePath, $this->content, LOCK_EX)
        };
    }

    /**
     * Create empty storage.
     *
     * @return bool
     * @throws StorageException
     */
    public function create(): bool
    {
        if (!is_readable(self::$path)) {
            throw new StorageException(self::$path . ' - not writable!');
        }

        return (bool)file_put_contents($this->filePath, null, LOCK_EX);
    }
}