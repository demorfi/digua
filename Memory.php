<?php

namespace Digua;

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
     * Semaphore key.
     *
     * @var int
     */
    private int $semKey;

    /**
     * Shared memory key.
     *
     * @var int
     */
    private int $shmKey;

    /**
     * Semaphore id.
     *
     * @var SysvSemaphore|false
     */
    private SysvSemaphore|bool $semId;

    /**
     * Shared memory id.
     *
     * @var SysvSharedMemory|false
     */
    private SysvSharedMemory|bool $shmId;

    /**
     * Offset key.
     *
     * @var int
     */
    private int $offset = 1;

    /**
     * Memory constructor.
     *
     * @param int $semKey
     * @param int $shmKey
     * @param int $size
     * @throws Exception
     */
    public function __construct(int $semKey, int $shmKey, int $size = 1024)
    {
        $this->semKey = $semKey;
        $this->shmKey = $shmKey;
        $this->size   = abs($size);

        $this->attach();
    }

    /**
     * Create memory.
     *
     * @param int $size
     * @return self
     * @throws Exception
     */
    public static function create(int $size = 1024): self
    {
        $semKey = random_int(time(), PHP_INT_MAX);
        $shmKey = random_int(time() + 1, PHP_INT_MAX);

        return (new self($semKey, $shmKey, $size));
    }

    /**
     * Restore memory.
     *
     * @param string $hash
     * @return self
     * @throws Exception
     */
    public static function restore(string $hash): self
    {
        if (!str_contains($hash, ':')) {
            throw new Exception('error restore hash!');
        }

        [$semKey, $shmKey, $size] = explode(':', $hash);
        return (new self($semKey, $shmKey, $size));
    }

    /**
     * Attach.
     *
     * @throws Exception
     */
    protected function attach(): void
    {
        $this->semId = sem_get($this->semKey);
        if ($this->semId === false) {
            throw new Exception('error creating semaphore!');
        }

        if (!sem_acquire($this->semId)) {
            sem_remove($this->semId);
            throw new Exception('error when trying to take a semaphore ' . $this->semId . '!');
        }

        $this->shmId = shm_attach($this->shmKey, $this->size);
        if ($this->shmId === false) {
            throw new Exception('error when connecting to shared memory!');
        }
    }

    /**
     * Detach memory.
     *
     * @throws Exception
     */
    public function detach(): void
    {
        if (!sem_release($this->semId)) {
            throw new Exception('error while trying to release the semaphore ' . $this->semId . '!');
        }

        if (!shm_remove($this->shmId)) {
            throw new Exception('error when trying to delete the shared memory segment ' . $this->shmId . '!');
        }

        if (!sem_remove($this->semId)) {
            throw new Exception('error when attempting to delete the semaphore ' . $this->semId . '!');
        }
    }

    /**
     * Detach memory.
     *
     * @throws Exception
     */
    public function free(): void
    {
        $this->detach();
    }

    /**
     * Get semaphore key.
     *
     * @return SysvSemaphore|false
     */
    public function getSemKey(): SysvSemaphore|bool
    {
        return ($this->semId);
    }

    /**
     * Get shared memory key.
     *
     * @return int
     */
    public function getShmKey(): int
    {
        return ($this->shmKey);
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function jsonSerialize(): string
    {
        return ($this->__toString());
    }

    /**
     * Get memory hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        return ($this->__toString());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return ($this->semKey . ':' . $this->shmKey . ':' . $this->size);
    }

    /**
     * Push.
     *
     * @param mixed $data
     * @throws Exception
     */
    public function push(mixed $data): void
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                if (!shm_put_var($this->shmId, $this->offset, [])) {
                    throw new Exception();
                }
            }

            $stack   = shm_get_var($this->shmId, $this->offset);
            $stack[] = $data;
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new Exception();
            }
        } catch (Exception) {
            sem_remove($this->semId);
            shm_remove($this->shmId);
            throw new Exception('error when trying to write to shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Pull.
     *
     * @return mixed
     * @throws Exception
     */
    public function pull(): mixed
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            $data = array_pop($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new Exception();
            }

            return ($data);
        } catch (Exception) {
            throw new Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Shift.
     *
     * @return mixed
     * @throws Exception
     */
    public function shift(): mixed
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            $data = array_shift($stack);
            if (!shm_put_var($this->shmId, $this->offset, $stack)) {
                throw new Exception();
            }

            return ($data);
        } catch (Exception) {
            throw new Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Read.
     *
     * @return bool|Generator
     * @throws Exception
     */
    public function read(): bool|Generator
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
     * Read.
     *
     * @return bool|array
     * @throws Exception
     */
    public function readToArray(): bool|array
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
            if ($stack === false) {
                throw new Exception();
            }

            if (sizeof($stack) < 1) {
                return (false);
            }

            return ($stack);
        } catch (Exception) {
            throw new Exception('error attempting to read from shared memory ' . $this->shmId . '!');
        }
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function size(): int
    {
        try {
            if (!shm_has_var($this->shmId, $this->offset)) {
                return (false);
            }

            $stack = shm_get_var($this->shmId, $this->offset);
        } catch (Exception) {
            return (0);
        }

        return ($stack !== false ? sizeof($stack) : 0);
    }
}
