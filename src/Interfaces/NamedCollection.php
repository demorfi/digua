<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Components\ArrayCollection;

interface NamedCollection
{
    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed;

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, mixed $value): void;

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool;

    /**
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void;

    /**
     * @param string $name
     * @param mixed  $default If request key not found it return default value
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed;

    /**
     * @param string $name  Name key
     * @param mixed  $value Value key
     */
    public function set(string $name, mixed $value): void;

    /**
     * @param string $name Name key
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * @return ArrayCollection
     */
    public function collection(): ArrayCollection;

    /**
     * @return array
     */
    public function getAll(): array;

    /**
     * @return array
     */
    public function getKeys(): array;

    /**
     * @return array
     */
    public function getValues(): array;

    /**
     * @return int
     */
    public function size(): int;

    /**
     * Flush array.
     */
    public function flush(): void;

    /**
     * Overwrite array.
     *
     * @param array $array
     */
    public function overwrite(array $array): void;
}