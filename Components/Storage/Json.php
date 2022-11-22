<?php

namespace Digua\Components\Storage;

use Digua\Enums\{ContentType, FileExtension};
use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};
use Digua\Traits\Data;
use Digua\Storage;
use JsonSerializable;

class Json extends Storage implements JsonSerializable
{
    use Data;

    /**
     * Storage full file path.
     *
     * @var string
     */
    protected string $filePath;

    /**
     * @param string $fileName Storage file name
     * @throws PathException
     * @throws StorageException
     */
    public function __construct(protected string $fileName)
    {
        parent::__construct($fileName . FileExtension::JSON->value, ContentType::JSON);
    }

    /**
     * Load and read storage.
     *
     * @param string $fileName Storage file name
     * @return self
     * @throws PathException
     * @throws StorageException
     */
    public static function load(string $fileName): self
    {
        $instance = new self($fileName);
        $instance->read();
        return $instance;
    }

    /**
     * Read storage.
     *
     * @return array
     * @throws StorageException
     */
    public function read(): array
    {
        return $this->array = (array)parent::read();
    }

    /**
     * Save storage.
     *
     * @return void
     * @throws StorageException
     */
    public function save(): void
    {
        parent::replace($this->array);
        parent::save();
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->array;
    }
}