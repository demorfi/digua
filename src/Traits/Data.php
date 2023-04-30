<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Components\ArrayCollection;

trait Data
{
    /**
     * @var array
     */
    protected array $array = [];

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->array[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, mixed $value): void
    {
        $this->array[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->array[$name]);
    }

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->array[$name]);
    }

    /**
     * @param string $name
     * @param mixed  $default If request key not found it return default value
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->array[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, mixed $value): void
    {
        $this->array[$name] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->__isset($name);
    }

    /**
     * @return ArrayCollection
     */
    public function collection(): ArrayCollection
    {
        return ArrayCollection::make($this->array);
    }

    /**
     * Get all list.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->array;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->array);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->array);
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return sizeof($this->array);
    }

    /**
     * Flush data.
     */
    public function flush(): void
    {
        $this->array = [];
    }

    /**
     * Overwrite data.
     *
     * @param array $array
     */
    public function overwrite(array $array): void
    {
        $this->array = $array;
    }
}
