<?php declare(strict_types=1);

namespace Digua\Components\Storage;

use Digua\Helper;
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\{
    Memory as MemoryException,
    MemoryShared as MemorySharedException
};
use Shmop;
use JsonSerializable;
use ValueError;

class SharedMemory implements StorageInterface, JsonSerializable
{
    /**
     * @var string
     */
    private const FLAG_LOCK = "\1";

    /**
     * @var string
     */
    private const FLAG_UNLOCK = "\0";

    /**
     * @var string
     */
    private const FLAG_EOF = "\2";

    /**
     * @var int
     */
    public const DEFAULT_SIZE = 1024;

    /**
     * @var Shmop|false
     */
    private Shmop|false $shmId;

    /**
     * @var int
     */
    private int $offset = 2;

    /**
     * @param string $name
     * @param int    $size
     * @throws MemoryException
     */
    public function __construct(private readonly string $name, private readonly int $size = self::DEFAULT_SIZE)
    {
        $this->attach();
    }

    /**
     * @param int $size
     * @return self
     * @throws MemoryException
     */
    public static function create(int $size = self::DEFAULT_SIZE): self
    {
        return new self((string)Helper::makeIntHash(), $size);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return @shmop_open(crc32($name), 'a', 0, 0) instanceof Shmop;
    }

    /**
     * @return void
     * @throws MemorySharedException
     */
    private function attach(): void
    {
        $this->shmId = shmop_open(crc32($this->name), 'c', 0644, $this->size + $this->offset);
        if ($this->shmId === false) {
            throw new MemorySharedException('Error when connecting to shared memory through the key ' . $this->name);
        }
    }

    /**
     * @param bool|string $data
     * @return ?string
     */
    private function clean(bool|string $data): ?string
    {
        return !empty($data) ? rtrim($data, "\0") : null;
    }

    /**
     * @param string $data
     * @param        $size
     * @return void
     * @throws MemorySharedException
     */
    private function allowedSize(string $data, &$size = null): void
    {
        $size = mb_strlen($data);
        if ($size > $this->size) {
            throw new MemorySharedException(
                sprintf(
                    'The allowed memory limit has been exceeded. Requested %d out of %d possible',
                    $size,
                    $this->size
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function free(): bool
    {
        return shmop_delete($this->shmId);
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
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        return $this->name . ':' . $this->size;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function read(): ?string
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

            return $this->clean(shmop_read($this->shmId, $this->offset, $this->size));
        }
    }

    /**
     * @inheritdoc
     * @throws MemorySharedException
     */
    public function write(string $data): bool
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

            $data = $this->clean(shmop_read($this->shmId, $this->offset, $this->size)) . $data;
            $this->allowedSize($data, $dataSize);
            $data .= str_repeat("\0", $this->size - $dataSize);

            try {
                shmop_write($this->shmId, self::FLAG_LOCK, 0);
                shmop_write($this->shmId, $data, $this->offset);
            } catch (ValueError $e) {
                throw new MemorySharedException('Error when trying to write to shared memory - ' . $e->getMessage());
            } finally {
                shmop_write($this->shmId, self::FLAG_UNLOCK, 0);
            }

            return true;
        }
    }

    /**
     * @inheritdoc
     * @throws MemorySharedException
     */
    public function rewrite(string|callable $data): bool
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

            try {
                shmop_write($this->shmId, self::FLAG_LOCK, 0);
                $data = is_callable($data)
                    ? (string)$data($this->clean(shmop_read($this->shmId, $this->offset, $this->size)))
                    : $data;

                $this->allowedSize($data, $dataSize);
                $data .= str_repeat("\0", $this->size - $dataSize);

                shmop_write($this->shmId, $data, $this->offset);
            } catch (MemoryException|ValueError $e) {
                throw new MemorySharedException('Error when trying to write to shared memory - ' . $e->getMessage());
            } finally {
                shmop_write($this->shmId, self::FLAG_UNLOCK, 0);
            }

            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        return shmop_read($this->shmId, 1, 1) === self::FLAG_EOF;
    }

    /**
     * @inheritdoc
     */
    public function setEof(): bool
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

            return (bool)shmop_write($this->shmId, self::FLAG_EOF, 1);
        }
    }
}