<?php

namespace Digua\Interfaces;

interface Stack
{
    /**
     * @param mixed $data
     * @return bool
     */
    public function push(mixed $data): bool;

    /**
     * @return mixed
     */
    public function pull(): mixed;

    /**
     * @return mixed
     */
    public function shift(): mixed;

    /**
     * @return iterable|false
     */
    public function read(): iterable|false;

    /**
     * @return iterable|false
     */
    public function readReverse(): iterable|false;

    /**
     * @return iterable
     */
    public function shadow(): iterable;

    /**
     * @return int
     */
    public function size(): int;
}