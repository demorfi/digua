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
     * @var array|string|null
     */
    protected array|string|null $content = null;

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
     * Get storage file name.
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get storage file path.
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Get storage content type.
     *
     * @return ContentType
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * Get storage content.
     *
     * @return array|string|null
     */
    public function getContent(): array|string|null
    {
        return $this->content;
    }

    /**
     * Read storage content.
     *
     * @return array|string|null
     * @throws StorageException
     */
    public function read(): array|string|null
    {
        if (!is_readable($this->filePath)) {
            throw new StorageException($this->filePath . ' - file is not readable!');
        }

        $content = match ($this->contentType) {
            ContentType::JSON => json_decode(file_get_contents($this->filePath), true),
            default => file_get_contents($this->filePath)
        };

        return $this->content = (is_bool($content) ? null : $content);
    }

    /**
     * Set storage content.
     *
     * @param array|string $content
     * @return void
     */
    public function replace(array|string $content): void
    {
        $this->content = $content;
    }

    /**
     * Append storage content.
     *
     * @param array|string $content
     * @return void
     */
    public function append(array|string $content): void
    {
        $this->content = match ($this->contentType) {
            ContentType::JSON => $this->content + $content,
            default => $this->content . $content
        };
    }

    /**
     * Rewrite storage content.
     *
     * @param bool $rewrite Add to the end of the storage or overwrite completely
     * @return void
     * @throws StorageException
     */
    public function save(bool $rewrite = true): void
    {
        if (!is_writable($this->filePath)) {
            throw new StorageException($this->filePath . ' - not writable!');
        }

        $content = $this->content;
        switch ($this->contentType) {
            case (ContentType::JSON):
                file_put_contents(
                    $this->filePath,
                    json_encode(!$rewrite ? array_merge($this->read(), $content) : $content),
                    LOCK_EX
                );
                break;
            default:
                file_put_contents($this->filePath, $content, (!$rewrite ? FILE_APPEND : 0) | LOCK_EX);
        }
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