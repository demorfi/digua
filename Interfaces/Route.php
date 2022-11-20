<?php

namespace Digua\Interfaces;

interface Route
{
    /**
     * @param Route $route
     * @return Response
     */
    public function delegate(Route $route): Response;
}