<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Traits\Data;
use Digua\Interfaces\NamedCollection;
use IteratorAggregate;
use Traversable;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use Countable;

class ArrayCollection implements NamedCollection, Countable, ArrayAccess, IteratorAggregate, JsonSerializable
{
    use Data;

    /**
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        $this->array = $values;
    }

    /**
     * @param array $values
     * @return static
     */
    public static function make(array $values = []): static
    {
        return new static($values);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return $this->size();
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize(): array
    {
        return $this->array;
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->array);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * @param string ...$names
     * @return static
     */
    public function only(string ...$names): static
    {
        $array = [];
        foreach ($names as $name) {
            $array[$name] = $this->array[$name] ?? null;
        }

        return static::make($array);
    }

    /**
     * Slice array by key.
     *
     * @param string    $prefix
     * @param ?callable $callable
     * @return static
     */
    public function slice(string $prefix, callable $callable = null): static
    {
        $array = [];
        foreach ($this->array as $key => $value) {
            if (($pos = stripos($key, $prefix)) !== false && (is_null($callable) || $callable($key, $value))) {
                $array[substr($key, ($pos + strlen($prefix)))] = $value;
            }
        }

        return static::make($array);
    }

    /**
     * @param ?callable $callable
     * @param int       $mode
     * @return static
     */
    public function filter(?callable $callable = null, int $mode = ARRAY_FILTER_USE_BOTH): static
    {
        return static::make(array_filter($this->array, $callable, $mode));
    }
}