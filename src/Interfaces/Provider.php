<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Provider
{
    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool;

    /**
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function get(string $key, string $type): mixed;
}