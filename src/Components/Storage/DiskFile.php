<?php declare(strict_types=1);

namespace Digua\Components\Storage;

use Digua\Helper;
use Digua\Traits\{Configurable, DiskPath};
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};

class DiskFile implements StorageInterface
{
    use Configurable, DiskPath;

    /**
     * @var string
     */
    private const FLAG_EOF = "\2";

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/storage'
    ];

    /**
     * @var int
     */
    private int $offset = 2;

    /**
     * @var string
     */
    protected string $filePath;

    /**
     * @param string $name Storage file name
     * @throws PathException
     * @throws StorageException
     */
    public function __construct(private readonly string $name)
    {
        self::throwIsBrokenDiskPath();
        $this->filePath = self::getDiskPath(Helper::filterFileName($this->name));

        if (!is_file($this->filePath)) {
            $this->empty();
        }
    }

    /**
     * @param string $name
     * @return self
     * @throws PathException
     * @throws StorageException
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->filePath;
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function read(): ?string
    {
        if (!is_readable($this->filePath)) {
            throw new StorageException($this->filePath . ' - file is not readable!');
        }

        return file_get_contents($this->filePath, false, null, $this->offset) ?: null;
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function write(string $data): bool
    {
        if (!is_writable($this->filePath)) {
            throw new StorageException($this->filePath . ' - not writable!');
        }

        return (bool)file_put_contents($this->filePath, $data, FILE_APPEND | LOCK_EX);
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function rewrite(string|callable $data): bool
    {
        if (!is_writable($this->filePath)) {
            throw new StorageException($this->filePath . ' - not writable!');
        }

        $content = file_get_contents($this->filePath);
        $data    = is_callable($data) ? (string)$data(substr($content, $this->offset)) : $data;
        return (bool)file_put_contents($this->filePath, substr($content, 0, $this->offset) . $data, LOCK_EX);
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function free(): bool
    {
        if (!self::isReadableDiskPath()) {
            throw new StorageException(self::getDiskPath() . ' - not writable!');
        }

        return unlink($this->filePath);
    }

    /**
     * @throws StorageException
     */
    private function empty(): bool
    {
        if (!self::isReadableDiskPath()) {
            throw new StorageException(self::getDiskPath() . ' - not writable!');
        }

        return (bool)file_put_contents($this->filePath, "\0\0", LOCK_EX);
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        $data = file_get_contents($this->filePath, false, null, 0, 1);
        return strcmp($data, self::FLAG_EOF) === 0;
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function setEof(): bool
    {
        if (!is_writable($this->filePath)) {
            throw new StorageException($this->filePath . ' - not writable!');
        }

        $data = substr_replace(file_get_contents($this->filePath), self::FLAG_EOF, 0, 1);
        return (bool)file_put_contents($this->filePath, $data, LOCK_EX);
    }
}