<?php declare(strict_types=1);

namespace Digua\Traits;

use BadMethodCallException;
use Digua\Exceptions\{
    Memory as MemoryException,
    MemoryShared as MemorySharedException
};
use Digua\Components\Memory;
use Generator;

/**
 * @mixin Memory
 */
trait Stack
{
    /**
     * Stack size.
     *
     * @var int
     */
    private int $defaultSize = 1024;

    /**
     * @var Memory
     */
    private Memory $memory;

    /**
     * @param string|null $hash
     * @throws MemoryException
     */
    public function __construct(?string $hash = null)
    {
        $size         = ($this->size ?? $this->defaultSize);
        $this->memory = !is_null($hash)
            ? Memory::restore($hash . ':' . $size)
            : Memory::create($size);
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        [$hash,] = explode(':', $this->memory->getHash());
        return $hash;
    }

    /**
     * @return string
     * @throws MemoryException
     */
    public static function genHash(): string
    {
        [$hash,] = explode(':', Memory::genHash());
        return $hash;
    }

    /**
     * @return array
     */
    protected function get(): array
    {
        $stack = $this->memory->read();
        return !empty($stack) ? unserialize($stack) : [];
    }

    /**
     * @param mixed $data
     * @return bool
     * @throws MemorySharedException
     */
    public function push(mixed $data): bool
    {
        return $this->memory->rewrite(function ($stack) use ($data) {
            $stack   = !empty($stack) ? unserialize($stack) : [];
            $stack[] = $data;
            return serialize($stack);
        });
    }

    /**
     * @return mixed
     * @throws MemorySharedException
     */
    public function pull(): mixed
    {
        $this->memory->rewrite(function ($stack) use (&$data) {
            $stack = !empty($stack) ? unserialize($stack) : [];
            $data  = sizeof($stack) < 1 ? false : array_pop($stack);
            return serialize($stack);
        });

        return $data;
    }

    /**
     * @return mixed
     * @throws MemorySharedException
     */
    public function shift(): mixed
    {
        $this->memory->rewrite(function ($stack) use (&$data) {
            $stack = !empty($stack) ? unserialize($stack) : [];
            $data  = sizeof($stack) < 1 ? false : array_shift($stack);
            return serialize($stack);
        });

        return $data;
    }

    /**
     * @return Generator|false
     * @throws MemorySharedException
     */
    public function read(): Generator|false
    {
        while (true) {
            $data = $this->pull();
            if (!$data) {
                return false;
            }

            yield $data;
        }
    }

    /**
     * @return Generator|false
     * @throws MemorySharedException
     */
    public function readReverse(): Generator|false
    {
        while (true) {
            $data = $this->shift();
            if (!$data) {
                return false;
            }

            yield $data;
        }
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return sizeof($this->get());
    }

    /**
     * Proxy memory callable.
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->memory, $name)) {
            throw new BadMethodCallException('method ' . $name . ' does not exist!');
        }

        return $this->memory->$name(...$arguments);
    }
}
