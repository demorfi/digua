<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Traits\Data;
use Digua\Enums\FileExtension;
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\Storage as StorageException;
use BadMethodCallException;
use JsonSerializable;

/**
 * @mixin Storage|StorageInterface
 */
class DataFile implements JsonSerializable
{
    use Data;

    /**
     * @var Storage|StorageInterface
     */
    protected readonly Storage|StorageInterface $storage;

    /**
     * @param string $fileName Storage file name
     * @throws StorageException
     */
    public function __construct(protected string $fileName)
    {
        $this->storage = Storage::makeDiskFile($this->fileName . FileExtension::JSON->value);
    }

    /**
     * @param string $fileName Storage file name
     * @return self
     * @throws StorageException
     */
    public static function create(string $fileName): self
    {
        return new self($fileName);
    }

    /**
     * @return array
     * @throws StorageException
     */
    public function read(): array
    {
        $this->array = (array)json_decode((string)$this->storage->read(), true);
        return $this->array;
    }

    /**
     * @param array $data
     * @return bool
     * @throws StorageException
     */
    public function write(array $data): bool
    {
        $this->array = array_merge($this->array, $data);
        return $this->storage->rewrite(json_encode($this->array));
    }

    /**
     * @param array|callable $data
     * @return bool
     * @throws StorageException
     */
    public function rewrite(array|callable $data): bool
    {
        return $this->storage->rewrite(function ($fileData) use ($data) {
            $fileData    = (array)json_decode((string)$fileData, true);
            $this->array = is_callable($data) ? (array)$data($fileData, $this->array) : $data;
            return json_encode($this->array);
        });
    }

    /**
     * @return bool
     * @throws StorageException
     */
    public function save(): bool
    {
        return $this->storage->rewrite(json_encode($this->array));
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->array;
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!is_callable([$this->storage, $name])) {
            throw new BadMethodCallException('method ' . $name . ' does not exist!');
        }

        return $this->storage->$name(...$arguments);
    }
}