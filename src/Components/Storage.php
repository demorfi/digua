<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\Storage as StorageException;
use Digua\Components\Storage\{
    DiskFile as DiskFileStorage,
    SharedMemory as SharedMemoryStorage
};
use Digua\Exceptions\BadMethodCall as BadMethodCallException;

/**
 * @mixin StorageInterface
 */
class Storage
{
    /**
     * @var StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * @param string $storageName
     * @param mixed  ...$arguments
     * @throws StorageException
     */
    public function __construct(string $storageName, mixed ...$arguments)
    {
        if (!is_subclass_of($storageName, StorageInterface::class)) {
            throw new StorageException($storageName . ' - storage not found!');
        }

        $this->storage = new $storageName(...$arguments);
    }

    /**
     * @param string $storage
     * @param mixed  ...$arguments
     * @return static
     * @throws StorageException
     */
    public static function make(string $storage, mixed ...$arguments): self
    {
        return new self($storage, ...$arguments);
    }

    /**
     * @param ...$arguments
     * @return DiskFileStorage|self
     * @throws StorageException
     */
    public static function makeDiskFile(...$arguments): self|DiskFileStorage
    {
        return new self(DiskFileStorage::class, ...$arguments);
    }

    /**
     * @param ...$arguments
     * @return SharedMemoryStorage|self
     * @throws StorageException
     */
    public static function makeSharedMemory(...$arguments): self|SharedMemoryStorage
    {
        return new self(SharedMemoryStorage::class, ...$arguments);
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!is_callable([$this->storage, $name])) {
            throw new BadMethodCallException('method ' . $name . ' does not exist!');
        }

        return $this->storage->$name(...$arguments);
    }
}