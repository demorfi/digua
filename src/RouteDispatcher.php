<?php declare(strict_types=1);

namespace Digua;

use Digua\Exceptions\{
    Route as RouteException,
    Base as BaseException
};
use Digua\Interfaces\{
    Route as RouteInterface,
    Controller as ControllerInterface,
    RouteBuilder as RouteBuilderInterface
};
use Digua\Controllers\Error as ErrorController;
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};

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
    public function __construct(private readonly Request $request = new Request)
    {
    }

    /**
     * @param RouteBuilderInterface|null $builder
     * @return Response
     * @throws RouteException
     */
    public function default(?RouteBuilderInterface $builder = null): Response
    {
        return $this->try(
            new RouteAsName($builder ?? new RouteAsNameBuilder($this->request)),
            ErrorController::class,
            'default'
        );
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
        $this->route = $route;
        $this->request->setRoute($route);

        $controllerName = $this->route->getControllerName();
        $actionName     = $this->route->getControllerAction();

        if (empty($controllerName) || !is_subclass_of($controllerName, ControllerInterface::class)) {
            throw new RouteException($controllerName . ' - controller not found!');
        }

        $this->controller = new $controllerName($this->request);
        if (empty($actionName) || !method_exists($this->controller, $actionName)) {
            throw new RouteException($controllerName . '->' . $actionName . ' - action not found!');
        }

        $this->request->setController($this->controller);
        return Response::create(call_user_func([$this->controller, $actionName]));
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
