<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Interfaces\Route\Builder as RouteBuilder;

interface Route
{
    /**
     * @param RouteBuilder $builder
     */
    public function __construct(RouteBuilder $builder);

    /**
     * @return string|null
     */
    public function getControllerName(): ?string;

    /**
     * @return string|null
     */
    public function getControllerAction(): ?string;

    /**
     * Switch active controller.
     *
     * @param string $controller Controller name
     * @param string $action     Controller action name
     * @return void
     */
    public function switch(string $controller, string $action): void;

    /**
     * @param string $route
     * @return bool
     */
    public function hasRoute(string $route): bool;

    /**
     * @return RouteBuilder
     */
    public function builder(): RouteBuilder;

    /**
     * @return string|null
     */
    public function getBaseName(): ?string;

    /**
     * @return string|null
     */
    public function getBaseAction(): ?string;

    /**
     * @return string|null
     */
    public function getBasePath(): ?string;
}