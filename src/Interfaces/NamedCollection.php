<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface NamedCollection
{
    /**
     * Get value.
     *
     * @param string $name Name key
     * @return mixed
     */
    public function __get(string $name): mixed;

    /**
     * Set value.
     *
     * @param string $name  Name key
     * @param mixed  $value Value key
     */
    public function __set(string $name, mixed $value): void;

    /**
     * Isset key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * Get value.
     *
     * @param string $name    Name key
     * @param mixed  $default If request key not found it return default value
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * Set value.
     *
     * @param string $name  Name key
     * @param mixed  $value Value key
     */
    public function set(string $name, mixed $value): void;

    /**
     * Has key.
     *
     * @param string $name Name key
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Slice array by key.
     *
     * @param string        $prefix
     * @param callable|null $callable
     * @return array
     */
    public function slice(string $prefix, callable $callable = null): array;

    /**
     * Get all list.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * @return array
     */
    public function getKeys(): array;

    /**
     * @return int
     */
    public function size(): int;

    /**
     * Flush data.
     */
    public function flush(): void;

    /**
     * Overwrite data.
     *
     * @param array $array
     */
    public function overwrite(array $array): void;
}