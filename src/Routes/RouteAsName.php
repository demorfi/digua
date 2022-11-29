<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Interfaces\{
    Route as RouteInterface,
    RouteBuilder as RouteBuilderInterface
};

class RouteAsName implements RouteInterface
{
    /**
     * @var string|null
     */
    private ?string $controllerName;

    /**
     * @var string|null
     */
    private ?string $actionName;

    /**
     * @param RouteBuilderInterface $builder
     */
    public function __construct(private readonly RouteBuilderInterface $builder)
    {
        $this->controllerName = $this->builder->getControllerName();
        $this->actionName     = $this->builder->getActionName();
    }

    /**
     * @inheritdoc
     */
    public function switch(string $controller, string $action): void
    {
        $this->controllerName = $controller;
        $this->actionName     = $action;
    }

    /**
     * @inheritdoc
     */
    public function builder(): RouteBuilderInterface
    {
        return $this->builder;
    }

    /**
     * @inheritdoc
     */
    public function getControllerName(): ?string
    {
        if (!empty($this->controllerName)) {
            if (str_contains($this->controllerName, '\\')) {
                return $this->controllerName;
            }

            return (defined('APP_CONTROLLERS_PATH')
                    ? constant('APP_CONTROLLERS_PATH')
                    : '\App\Controllers\\') . ucfirst($this->controllerName);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getControllerAction(): ?string
    {
        return !empty($this->actionName) ? ($this->actionName . 'Action') : null;
    }

    /**
     * @inheritdoc
     */
    public function getBaseName(): ?string
    {
        return !empty($this->controllerName) ? strtolower(basename($this->controllerName)) : null;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAction(): ?string
    {
        return !empty($this->actionName) ? strtolower(basename($this->actionName)) : null;
    }

    /**
     * @inheritdoc
     */
    public function getBasePath(): ?string
    {
        $baseName   = $this->getBaseName();
        $baseAction = $this->getBaseAction();
        return !empty($baseName) && !empty($baseAction) ? ($baseName . '.' . $baseAction) : null;
    }

    /**
     * @inheritdoc
     */
    public function hasRoute(string $route): bool
    {
        $basePath = $this->getBasePath();
        return !empty($basePath) && preg_match('/^' . preg_quote($route, '/') . '/', $basePath) > 0;
    }
}