<?php declare(strict_types=1);

namespace Digua\Traits;

use Digua\Components\{ArrayCollection, Types};

trait Data
{
    /**
     * @var array
     */
    protected array $array = [];

    /**
     * @param int|string $key
     * @return mixed
     */
    public function __get(int|string $key): mixed
    {
        return $this->array[$key] ?? null;
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function __set(int|string $key, mixed $value): void
    {
        $this->array[$key] = $value;
    }

    /**
     * @param int|string $key
     * @return bool
     */
    public function __isset(int|string $key): bool
    {
        return isset($this->array[$key]);
    }

    /**
     * @param int|string $key
     * @return void
     */
    public function __unset(int|string $key): void
    {
        if ($this->__isset($key)) {
            unset($this->array[$key]);
        }
    }

    /**
     * @param int|string $key
     * @param mixed      $default If request key not found it return default value
     * @return mixed
     */
    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->__get($key) ?? $default;
    }

    /**
     * @param int|string $key
     * @param ?mixed     $default
     * @return Types
     */
    public function getTypeValue(int|string $key, mixed $default = null): Types
    {
        return Types::value($this->get($key, $default));
    }

    /**
     * @param int|string $key
     * @param string     $type
     * @param ?mixed     $default
     * @return mixed
     * @uses getTypeValue
     */
    public function getFixedTypeValue(int|string $key, string $type, mixed $default = null): mixed
    {
        return $this->getTypeValue($key, $default)->to($type)->getValue();
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function set(int|string $key, mixed $value): void
    {
        $this->__set($key, $value);
    }

    /**
     * @param int|string $key
     * @return bool
     */
    public function has(int|string $key): bool
    {
        return $this->__isset($key);
    }

    /**
     * @param int|string $key
     * @return void
     */
    public function remove(int|string $key): void
    {
        $this->__unset($key);
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
     * @param array $array
     */
    public function overwrite(array $array): void
    {
        $this->array = $array;
    }
}
