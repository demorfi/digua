<?php

namespace Digua\Interfaces;

use Generator;

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
     * @return Generator|false
     */
    public function read(): Generator|false;

    /**
     * @return Generator|false
     */
    public function readReverse(): Generator|false;

    /**
     * @return Generator
     */
    public function shadow(): Generator;

    /**
     * @return int
     */
    public function size(): int;
}