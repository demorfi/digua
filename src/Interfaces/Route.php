<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Interfaces\Route\Builder as RouteBuilder;

interface Route
{
    /**
     * @param string $appEntryPath
     * @param RouteBuilder $builder
     */
    public function __construct(string $appEntryPath, RouteBuilder $builder);

    /**
     * @return ?string
     */
    public function getControllerName(): ?string;

    /**
     * @return ?string
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
     * @param Controller $controller
     * @return bool
     */
    public function isPermitted(Controller $controller): bool;

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
     * @param Controller $controller
     * @return array
     */
    public function provide(Controller $controller): array;

    /**
     * @return ?string
     */
    public function getBaseName(): ?string;

    /**
     * @return ?string
     */
    public function getBaseAction(): ?string;

    /**
     * @return ?string
     */
    public function getBasePath(): ?string;

    /**
     * @return string
     */
    public function getEntryPath(): string;
}