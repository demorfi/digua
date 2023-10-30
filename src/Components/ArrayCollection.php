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
     * @return mixed
     */
    public function first(): mixed
    {
        return $this->array[array_key_first($this->array)] ?? null;
    }

    /**
     * @return mixed
     */
    public function last(): mixed
    {
        return $this->array[array_key_last($this->array)] ?? null;
    }

    /**
     * @return int|string|null
     */
    public function firstKey(): int|string|null
    {
        return array_key_first($this->array);
    }

    /**
     * @return int|string|null
     */
    public function lastKey(): int|string|null
    {
        return array_key_last($this->array);
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
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    /**
     * @param int|string ...$keys
     * @return static
     */
    public function only(int|string ...$keys): static
    {
        $array = [];
        foreach ($keys as $key) {
            $array[$key] = $this->array[$key] ?? null;
        }

        return static::make($array);
    }

    /**
     * @param int|string ...$keys
     * @return static
     */
    public function except(int|string ...$keys): static
    {
        return $this->filter(static fn($value, $key) => !in_array($key, $keys));
    }

    /**
     * @param int|string ...$keys
     * @return static
     */
    public function collapse(int|string ...$keys): static
    {
        $collection = static::make($this->array);
        foreach ($keys as $key) {
            $collection->overwrite($collection->get($key, []));
        }
        return $collection;
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

    /**
     * @param array $array
     * @param bool  $recursive
     * @return static
     */
    public function merge(array $array, bool $recursive = false): static
    {
        return static::make(call_user_func('array_merge' . ($recursive ? '_recursive' : ''), $this->array, $array));
    }

    /**
     * @param callable $callable
     * @param bool     $recursive
     * @return static
     */
    public function each(callable $callable, bool $recursive = false): static
    {
        $array = $this->array;
        call_user_func_array('array_walk' . ($recursive ? '_recursive' : ''), [&$array, $callable]);
        return static::make($array);
    }

    /**
     * @param int|string $key
     * @param callable   $callable
     * @param bool       $recursive
     * @return static
     */
    public function replaceValue(int|string $key, callable $callable, bool $recursive = false): static
    {
        return $this->each(static fn(&$v, $k) => $k === $key ? $v = $callable($v) : null, $recursive);
    }

    /**
     * @param mixed $needle
     * @param bool  $strict
     * @param bool  $recursive
     * @return static
     */
    public function search(mixed $needle, bool $strict = false, bool $recursive = false): static
    {
        $array = [];
        foreach($this->array as $key => $value) {
            if (($strict && $value === $needle) || (!$strict && $value == $needle)) {
                $array = [$key => $value];
                break;
            }

            if ($recursive && is_array($value)) {
                $result = static::make($value)->search($needle, $strict, $recursive);
                if (!$result->isEmpty()) {
                    $array = [$key => $result];
                    break;
                }
            }
        }

        return static::make($array);
    }
}