<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Interfaces\Route as RouteInterface;
use Digua\Request;

class RouteAsName implements RouteInterface
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var string
     */
    private string $controllerName;

    /**
     * @var string
     */
    private string $actionName;

    /**
     * @param Request     $request
     * @param string|null $controller Forced controller
     * @param string|null $action     Forced controller action
     */
    public function __construct(Request $request, ?string $controller = null, ?string $action = null)
    {
        $this->request        = $request;
        $this->controllerName = $controller ?: $this->request->getQuery()->getName();
        $this->actionName     = $action ?: $this->request->getQuery()->getAction();
    }

    /**
     * @inheritdoc
     */
    public function getControllerName(): string
    {
        return str_contains($this->controllerName, '\\')
            ? $this->controllerName
            : ('\App\Controllers\\' . ucfirst($this->controllerName));
    }

    /**
     * @inheritdoc
     */
    public function getControllerAction(): string
    {
        return $this->actionName . 'Action';
    }

    /**
     * @inheritdoc
     */
    public function switch(string $controller, string $action): void
    {
        $this->controllerName = $controller;
        $this->actionName     = $action;
    }
}