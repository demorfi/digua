<?php declare(strict_types = 1);

namespace Digua\Interfaces;

interface Route
{
    /**
     * @param Route $route
     * @return Response
     */
    public function delegate(Route $route): Response;
}