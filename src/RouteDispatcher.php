<?php declare(strict_types=1);

namespace Digua;

use Digua\Exceptions\{
    Route as RouteException,
    Base as BaseException
};
use Digua\Interfaces\{
    Route as RouteInterface,
    Controller as ControllerInterface
};
use Digua\Controllers\Error as ErrorController;
use Digua\Routes\RouteAsName;

class RouteDispatcher
{
    /**
     * @var RouteInterface
     */
    private RouteInterface $route;

    /**
     * @var ControllerInterface
     */
    private ControllerInterface $controller;

    /**
     * @param Request $request
     */
    public function __construct(private readonly Request $request = new Request())
    {
    }

    /**
     * @param string|null $controller Forced controller
     * @param string|null $action     Forced controller action
     * @return Response
     * @throws RouteException
     */
    public function default(string $controller = null, string $action = null): Response
    {
        return $this->try(new RouteAsName($this->request, $controller, $action), ErrorController::class, 'default');
    }

    /**
     * @return RouteInterface
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface
    {
        return $this->controller;
    }

    /**
     * @param RouteInterface $route
     * @return Response
     * @throws RouteException
     */
    public function delegate(RouteInterface $route): Response
    {
        $this->route    = $route;
        $controllerName = $this->route->getControllerName();
        $actionName     = $this->route->getControllerAction();

        if (!is_subclass_of($controllerName, ControllerInterface::class)) {
            throw new RouteException($controllerName . ' - controller not found!');
        }

        $this->controller = new $controllerName($this->request);
        if (!method_exists($this->controller, $actionName)) {
            throw new RouteException($controllerName . '->' . $actionName . ' - action not found!');
        }

        return Response::create(call_user_func([$this->controller, $actionName]))->build();
    }

    /**
     * @param RouteInterface $route
     * @param string         $controller Alternative controller
     * @param string         $action     Alternative controller action
     * @return Response
     * @throws RouteException
     */
    public function try(RouteInterface $route, string $controller, string $action): Response
    {
        try {
            return $this->delegate($route);
        } catch (BaseException) {
            try {
                $route->switch($controller, $action);
                return $this->delegate($route);
            } catch (BaseException $e) {
                header('HTTP/1.1 500 Internal Server Error');
                throw new RouteException($e->getMessage());
            }
        }
    }
}
