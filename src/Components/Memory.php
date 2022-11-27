<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Exceptions\{
    Memory as MemoryException,
    MemorySemaphore as MemorySemaphoreException,
    MemoryShared as MemorySharedException
};
use SysvSemaphore;
use SysvSharedMemory;
use JsonSerializable;
use Generator;
use Exception;

class Memory implements JsonSerializable
{
    /**
     * Memory size.
     *
     * @var int|float
     */
    private int|float $size;

    /**
     * @var int
     */
    private int $semKey;

    /**
     * @var int
     */
    private int $shmKey;

    /**
     * @var SysvSemaphore|false
     */
    private SysvSemaphore|false $semId;

    /**
     * @var SysvSharedMemory|false
     */
    private SysvSharedMemory|false $shmId;

    /**
     * @var int
     */
    private int $offset = 1;

    /**
     * @param int $semKey
     * @param int $shmKey
     * @param int $size
     * @throws MemoryException
     */
    public function __construct(int $semKey, int $shmKey, int $size = 1024)
    {
        $this->semKey = $semKey;
        $this->shmKey = $shmKey;
        $this->size   = abs($size);

        $this->attach();
    }

    /**
     * @param int $size
     * @return self
     * @throws MemoryException
     */
    public static function create(int $size = 1024): self
    {
        try {
            $semKey = random_int(time(), PHP_INT_MAX);
            $shmKey = random_int(time() + 1, PHP_INT_MAX);
        } catch (Exception $e) {
            throw new MemoryException($e->getMessage());
        }

        return new self($semKey, $shmKey, $size);
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

        [$semKey, $shmKey, $size] = explode(':', $hash);
        return new self((int)$semKey, (int)$shmKey, (int)$size);
    }

    /**
     * @throws MemoryException
     */
    protected function attach(): void
    {
        $this->semId = sem_get($this->semKey);
        if ($this->semId === false) {
            throw new MemorySemaphoreException('Error creating semaphore!');
        }

        if (!sem_acquire($this->semId)) {
            sem_remove($this->semId);
            throw new MemorySemaphoreException('Error when trying to take a semaphore ' . $this->semId . '!');
        }

        $this->shmId = shm_attach($this->shmKey, $this->size);
        if ($this->shmId === false) {
            throw new MemorySharedException('Error when connecting to shared memory!');
        }
    }

    /**
     * @throws MemoryException
     */
    public function detach(): void
    {
        if (!sem_release($this->semId)) {
            throw new MemorySemaphoreException('Error while trying to release the semaphore ' . $this->semId . '!');
        }

        if (!shm_remove($this->shmId)) {
            throw new MemorySharedException(
                'Error when trying to delete the shared memory segment ' . $this->shmId . '!'
            );
        }

        if (!sem_remove($this->semId)) {
            throw new MemorySemaphoreException('Error when attempting to delete the semaphore ' . $this->semId . '!');
        }
    }

    /**
     * @throws MemoryException
     */
    public function free(): void
    {
        $this->detach();
    }

    /**
     * @return SysvSemaphore|false
     */
    public function getSemKey(): SysvSemaphore|false
    {
        return $this->semId;
    }

    /**
     * @return int
     */
    public function getShmKey(): int
    {
        return $this->shmKey;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->semKey . ':' . $this->shmKey . ':' . $this->size;
    }

    /**
     * @param mixed $data
     * @throws MemorySharedException
     */
    public function push(mixed $data): void
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                if (!shm_put_var($this->shmId, $this->offset, [])) {
                    throw new MemoryException;
                }
            }

            $stack   = shm_get_var($this->shmId, $this->offset);
            $stack[] = $data;
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new MemoryException;
            }
        } catch (MemoryException) {
            sem_remove($this->semId);
            shm_remove($this->shmId);
            throw new MemorySharedException('Error when trying to write to shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * @return mixed
     * @throws MemorySharedException
     */
    protected function get(): mixed
    {
        if (!shm_has_var($this->shmId, $this->offset)) {
            return false;
        }

        $stack = shm_get_var($this->shmId, $this->offset);
        if ($stack === false) {
            throw new MemorySharedException;
        }

        return $stack;
    }

    /**
     * @return mixed
     * @throws MemorySharedException
     */
    public function pull(): mixed
    {
        try {
            $stack = $this->get();
            if (is_bool($stack) || sizeof($stack) < 1) {
                return false;
            }

            $data = array_pop($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new MemoryException;
            }

            return $data;
        } catch (MemoryException) {
            throw new MemorySharedException('Error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * @return mixed
     * @throws MemorySharedException
     */
    public function shift(): mixed
    {
        try {
            $stack = $this->get();
            if (is_bool($stack) || sizeof($stack) < 1) {
                return false;
            }

            $data = array_shift($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new MemoryException;
            }

            return $data;
        } catch (MemoryException) {
            throw new MemorySharedException('Error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * @return Generator|false
     * @throws MemorySharedException
     */
    public function read(): Generator|false
    {
        while (true) {
            $data = $this->shift();
            if (!$data) {
                return false;
            }

            yield $data;
        }
    }

    /**
     * @return array|false
     * @throws MemorySharedException
     */
    public function readToArray(): array|false
    {
        try {
            $stack = $this->get();
            if (is_bool($stack) || sizeof($stack) < 1) {
                return false;
            }

            return $stack;
        } catch (MemoryException) {
            throw new MemorySharedException('Error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * @return int
     */
    public function size(): int
    {
        if (!shm_has_var($this->shmId, $this->offset)) {
            return 0;
        }

        $stack = shm_get_var($this->shmId, $this->offset);
        return $stack !== false ? sizeof($stack) : 0;
    }
}
