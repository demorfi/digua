<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Traits\Data;
use Digua\Enums\FileExtension;
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\{
    Storage as StorageException,
    BadMethodCall as BadMethodCallException
};
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
    protected Storage|StorageInterface $storage;

    /**
     * @param string $fileName Storage file name
     * @throws StorageException
     */
    public function __construct(protected readonly string $fileName)
    {
        $this->init();
    }

    /**
     * @throws StorageException
     */
    protected function init(): void
    {
        $this->storage = Storage::makeDiskFile($this->fileName . FileExtension::JSON->value);
    }

    /**
     * @param string $fileName Storage file name
     * @return static
     * @throws StorageException
     */
    public static function create(string $fileName): static
    {
        return new static($fileName);
    }

    /**
     * @return array
     */
    public function read(): array
    {
        $this->array = (array)json_decode((string)$this->storage->read(), true);
        return $this->array;
    }

    /**
     * @param array $data
     * @return bool
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
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!is_callable([$this->storage, $name])) {
            throw new BadMethodCallException(sprintf('Method (%s) does not exist!', $name));
        }

        return $this->storage->$name(...$arguments);
    }
}