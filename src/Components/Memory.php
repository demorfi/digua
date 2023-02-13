<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Exceptions\{
    Memory as MemoryException,
    MemoryShared as MemorySharedException
};
use Shmop;
use JsonSerializable;
use Exception;
use ValueError;

class Memory implements JsonSerializable
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
     * @param int $shmKey
     * @param int $size
     * @throws MemoryException
     */
    public function __construct(private readonly int $shmKey, private readonly int $size = self::DEFAULT_SIZE)
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
        [$shmKey, $size] = explode(':', self::genHash($size));
        return new self((int)$shmKey, (int)$size);
    }

    /**
     * @param string $hash
     * @return self
     * @throws MemoryException
     */
    public static function restore(string $hash): self
    {
        if (!str_contains($hash, ':')) {
            throw new MemoryException('Error restore hash!');
        }

        [$shmKey, $size] = explode(':', $hash);
        return new self((int)$shmKey, (int)$size);
    }

    /**
     * @param int $size
     * @return string
     * @throws MemoryException
     */
    public static function genHash(int $size = self::DEFAULT_SIZE): string
    {
        try {
            return random_int(time(), PHP_INT_MAX) . ':' . $size;
        } catch (Exception $e) {
            throw new MemoryException('Error generating memory key - ' . $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws MemorySharedException
     */
    private function attach(): void
    {
        $this->shmId = shmop_open($this->shmKey, 'c', 0644, $this->size + $this->offset);
        if ($this->shmId === false) {
            throw new MemorySharedException('Error when connecting to shared memory through the key ' . $this->shmKey);
        }
    }

    /**
     * @return bool
     */
    public function free(): bool
    {
        return shmop_delete($this->shmId);
    }

    /**
     * @return int
     */
    public function getKey(): int
    {
        return $this->shmKey;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getHash();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->shmKey . ':' . $this->size;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getHash();
    }

    /**
     * @return bool
     */
    public function hasEof(): bool
    {
        return shmop_read($this->shmId, 1, 1) === self::FLAG_EOF;
    }

    /**
     * @param bool|string $data
     * @return string
     */
    private function clean(bool|string $data): string
    {
        return !empty($data) ? rtrim($data, "\0") : '';
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
     * @return string
     */
    public function read(): string
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }
            return $this->clean(shmop_read($this->shmId, $this->offset, $this->size));
        }
    }

    /**
     * @param string $data
     * @return bool
     * @throws MemorySharedException
     */
    public function write(string $data): bool
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

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
     * @param callable $callable
     * @return bool
     * @throws MemorySharedException
     */
    public function rewrite(callable $callable): bool
    {
        while (true) {
            if (shmop_read($this->shmId, 0, 1) === self::FLAG_LOCK) {
                continue;
            }

            try {
                shmop_write($this->shmId, self::FLAG_LOCK, 0);
                $rewriteData = (string)$callable($this->clean(shmop_read($this->shmId, $this->offset, $this->size)));

                $this->allowedSize($rewriteData, $dataSize);
                $rewriteData .= str_repeat("\0", $this->size - $dataSize);

                shmop_write($this->shmId, $rewriteData, $this->offset);
            } catch (MemoryException|ValueError $e) {
                throw new MemorySharedException('Error when trying to write to shared memory - ' . $e->getMessage());
            } finally {
                shmop_write($this->shmId, self::FLAG_UNLOCK, 0);
            }

            return true;
        }
    }

    /**
     * @return bool
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
