<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Interfaces\Request\Data as RequestData;
use Digua\Exceptions\Route as RouteException;

interface Request extends Service
{
    /**
     * @return RequestData
     */
    public function getData(): RequestData;

    /**
     * @param Route $route
     * @return void
     */
    public function setRoute(Route $route): void;

    /**
     * @return Route
     */
    public function getRoute(): Route;

    /**
     * @param Controller $controller
     * @return void
     */
    public function setController(Controller $controller): void;

    /**
     * @return Controller
     */
    public function getController(): Controller;

    /**
     * @param RouteException $exception
     * @return void
     */
    public function abort(RouteException $exception): void;

    /**
     * @return ?RouteException
     */
    public function getException(): ?RouteException;
}