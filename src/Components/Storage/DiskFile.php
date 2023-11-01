<?php declare(strict_types=1);

namespace Digua\Components\Storage;

use Digua\Helper;
use Digua\Components\File;
use Digua\Traits\DiskPath;
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException,
    File as FileException
};

class DiskFile implements StorageInterface
{
    use DiskPath;

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
     */
    public function __construct(private readonly string $name)
    {
        self::throwIsBrokenDiskPath();
        $this->filePath = self::getDiskPath(Helper::filterFileName($this->name));

        if (!is_file($this->filePath)) {
            file_put_contents($this->filePath, "\0\0", LOCK_EX);
        }
    }

    /**
     * @param string $name
     * @return self
     * @throws PathException
     */
    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return is_file(self::getDiskPath(Helper::filterFileName($name)));
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
        try {
            $file = new File($this->filePath);
            $file->readLock();
            $data = $file->readLeft($this->offset);
            $file->unlock();
            return $data ?: null;
        } catch (FileException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function write(string $data): bool
    {
        try {
            $file = new File($this->filePath);
            $file->writeLock();
            $result = $file->writeRight($data);
            $file->unlock();
            return $result;
        } catch (FileException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     * @throws StorageException
     */
    public function rewrite(string|callable $data): bool
    {
        try {
            $file = new File($this->filePath);
            $file->writeLock();
            if (is_callable($data)) {
                $data = (string)$data($file->readLeft($this->offset));
            }
            $file->truncate($this->offset);
            $result = $file->writeLeft($data, $this->offset);
            $file->unlock();
            return $result;
        } catch (FileException $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function free(): bool
    {
        return unlink($this->filePath);
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        try {
            $file = new File($this->filePath);
            $file->readLeft(0, 1);
            return strcmp($file->readLeft(0, 1), self::FLAG_EOF) === 0;
        } catch (FileException) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function setEof(): bool
    {
        try {
            $file = new File($this->filePath);
            $file->writeLock();
            $result = $file->writeLeft(self::FLAG_EOF, 0, 1);
            $file->unlock();
            return $result;
        } catch (FileException) {
            return false;
        }
    }
}