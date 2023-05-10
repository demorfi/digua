<?php declare(strict_types=1);

namespace Digua;

use Digua\Controllers\Error as ErrorController;
use Digua\Interfaces\{
    Controller as ControllerInterface,
    Route as RouteInterface,
    Route\Builder as RouteBuilderInterface
};
use Digua\Exceptions\{
    Base as BaseException,
    Route as RouteException
};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Exception;

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
     * @param ?RouteBuilderInterface $builder
     * @param ?string                $appEntryPath
     * @param ?string                $errorController
     * @return Response
     * @throws BaseException
     */
    public function default(
        ?RouteBuilderInterface $builder = null,
        ?string $appEntryPath = null,
        ?string $errorController = null
    ): Response {
        $defEntryPath = defined('APP_ENTRY_PATH') ? constant('APP_ENTRY_PATH') : '\App\Controllers\\';
        return $this->try(
            new RouteAsName(
                $appEntryPath ?? $defEntryPath,
                $builder ?? new RouteAsNameBuilder(new Request)
            ),
            $errorController ?? ErrorController::class
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
        $this->route->builder()->request()->setRoute($this->route);

        $controllerName = $this->route->getControllerName();
        if (empty($controllerName) || !is_subclass_of($controllerName, ControllerInterface::class)) {
            throw new RouteException($controllerName . ' - controller not found!');
        }

        $this->controller = new $controllerName($this->route->builder()->request());
        $this->route->builder()->request()->setController($this->controller);

        $actionName = $this->route->getControllerAction();
        if (empty($actionName) || !method_exists($this->controller, $actionName)) {
            throw new RouteException($controllerName . '->' . $actionName . ' - action not found!');
        }

        if (!$this->route->isPermitted($this->controller)) {
            throw new RouteException($controllerName . '->' . $actionName . ' - access not granted!');
        }

        return Response::create($this->controller->$actionName(...$this->route->provide($this->controller)));
    }

    /**
     * @param RouteInterface $route
     * @param string         $errorController Alternative controller
     * @param string         $errorAction     Alternative controller action
     * @return Response
     * @throws BaseException
     */
    public function try(RouteInterface $route, string $errorController, string $errorAction = 'default'): Response
    {
        try {
            try {
                return $this->delegate($route);
            } catch (RouteException $e) {
                $route->switch($errorController, $errorAction);
                $route->builder()->request()->abort($e);
                return $this->delegate($route);
            }
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            throw new BaseException($e->getMessage());
        }
    }
}
