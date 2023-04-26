<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Guardian
{
    /**
     * @param Route $route
     * @return bool
     */
    public function granted(Route $route): bool;
}