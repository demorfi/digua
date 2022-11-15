<?php

namespace Digua\Traits;

use Digua\Memory;
use Exception;

trait Stack
{
    /**
     * Stack size.
     *
     * @var int
     */
    protected int $defaultSize = 1024;

    /**
     * Memory instance.
     *
     * @var Memory
     */
    protected Memory $memory;

    /**
     * Stack constructor.
     *
     * @param string|null $hash
     * @throws Exception
     */
    public function __construct(string $hash = null)
    {
        $size = ($this->size ?? $this->defaultSize);
        if (!is_null($hash)) {
            $this->memory = Memory::restore($hash . ':' . $size);
        } else {
            $this->memory = Memory::create($size);
        }
    }

    /**
     * Get memory hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        $size = ($this->size ?? $this->defaultSize);
        return (preg_replace('/:' . $size . '$/', '', $this->memory->getHash()));
    }

    /**
     * Set finished flag.
     *
     * @throws Exception
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
        return ($data === -1);
    }

    /**
     * Free stack.
     *
     * @throws Exception
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
        return (call_user_func_array([$this->memory, $name], $arguments));
    }
}
