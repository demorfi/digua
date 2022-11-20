<?php

namespace Digua\Abstracts;

abstract class Data
{
    /**
     * Data.
     *
     * @var array
     */
    protected array $array = [];

    /**
     * Get value.
     *
     * @param string $name Name key
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->array[$name] ?? null;
    }

    /**
     * Set value.
     *
     * @param string $name  Name key
     * @param mixed  $value Value key
     */
    public function __set(string $name, mixed $value): void
    {
        $this->array[$name] = $value;
    }

    /**
     * Isset key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->array[$name]);
    }

    /**
     * Get value.
     *
     * @param string $name    Name key
     * @param mixed  $default If request key not found it return default value
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->array[$name] ?? $default;
    }

    /**
     * Set value.
     *
     * @param string $name  Name key
     * @param mixed  $value Value key
     */
    public function set(string $name, mixed $value): void
    {
        $this->array[$name] = $value;
    }

    /**
     * Has key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->__isset($name);
    }

    /**
     * Slice array by key.
     *
     * @param string        $prefix
     * @param callable|null $callable
     * @return array
     */
    public function slice(string $prefix, callable $callable = null): array
    {
        $array = [];
        foreach ($this->array as $key => $value) {
            if (($pos = stripos($key, $prefix)) !== false && (is_null($callable) || $callable($key, $value))) {
                $array[substr($key, ($pos + strlen($prefix)))] = $value;
            }
        }

        return $array;
    }

    /**
     * Get values.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->array;
    }

    /**
     * Get keys.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->array);
    }

    /**
     * Get size.
     *
     * @return int
     */
    public function size(): int
    {
        return sizeof($this->array);
    }

    /**
     * Flush values.
     */
    public function flush(): void
    {
        $this->array = [];
    }

    /**
     * Overwrite values.
     *
     * @param array $array
     */
    public function overwrite(array $array): void
    {
        $this->array = $array;
    }
}
