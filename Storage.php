<?php

namespace Digua;

use Digua\Traits\Data;
use JsonSerializable;
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;

class Storage implements JsonSerializable
{
    use Data;

    /**
     * Path to storage files.
     *
     * @var string
     */
    public static string $path = '';

    /**
     * Original data for diff.
     *
     * @var array
     */
    private array $original = [];

    /**
     * Storage name.
     *
     * @var string
     */
    private string $name;

    /**
     * Storage constructor.
     *
     * @param string $name Storage name
     * @throws PathException
     */
    public function __construct(string $name)
    {
        if (empty(static::$path)) {
            throw new PathException('the path to the storage is not configured');
        }

        $this->name = $name . FileExtension::JSON->value;
        if (file_exists(static::$path . $this->name)) {
            $this->array = $this->original = json_decode(file_get_contents(static::$path . $this->name), true);
        }
    }

    /**
     * Set path to storage files.
     *
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        static::$path = $path;
    }

    /**
     * Load storage.
     *
     * @param string $name Storage name
     * @return Storage
     * @throws PathException
     */
    public static function load(string $name): self
    {
        return new self($name);
    }

    /**
     * Save storage.
     */
    public function __destruct()
    {
        if (sizeof($this->array) != sizeof($this->original)
            || sizeof(@array_diff_assoc($this->array, $this->original))
        ) {
            file_put_contents(static::$path . $this->name, json_encode($this->array), LOCK_EX);
        }
    }

    /**
     * Save storage.
     */
    public function save(): void
    {
        file_put_contents(static::$path . $this->name, json_encode($this->array), LOCK_EX);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->array;
    }
}
