<?php

namespace Digua\Traits;

use Digua\Exceptions\{
    Memory as MemoryException,
    MemoryShared as MemorySharedException
};
use Digua\Memory;

trait Stack
{
    /**
     * Stack size.
     *
     * @var int
     */
    protected int $defaultSize = 1024;

    /**
     * @var Memory
     */
    protected Memory $memory;

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
     * Get memory hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        $size = ($this->size ?? $this->defaultSize);
        return preg_replace('/:' . $size . '$/', '', $this->memory->getHash());
    }

    /**
     * Set finished flag.
     *
     * @throws MemorySharedException
     */
    public function setEndFlag(): void
    {
        $this->memory->push(-1);
    }

    /**
     * Is finished flag.
     *
     * @param mixed $data
     * @return bool
     */
    public function isEndFlag(mixed $data): bool
    {
        return $data === -1;
    }

    /**
     * Free stack.
     *
     * @throws MemoryException
     */
    public function free(): void
    {
        $this->memory->free();
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
        return call_user_func_array([$this->memory, $name], $arguments);
    }
}
