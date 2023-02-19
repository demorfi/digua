<?php declare(strict_types=1);

namespace Digua;

use Digua\Interfaces\{
    Controller as ControllerInterface,
    Request as RequestInterface,
    Request\Data as RequestDataInterface,
    Route as RouteInterface
};
use Digua\Request\Data as DataRequest;

class Request implements RequestInterface
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
     * @param RequestDataInterface $dataRequest
     */
    public function __construct(private readonly RequestDataInterface $dataRequest = new DataRequest)
    {
    }

    /**
     * @inheritdoc
     */
    public function getData(): RequestDataInterface
    {
        return $this->dataRequest;
    }

    /**
     * @inheritdoc
     */
    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    /**
     * @inheritdoc
     */
    public function setRoute(RouteInterface $route): void
    {
        $this->route = $route;
    }

    /**
     * @inheritdoc
     */
    public function setController(ControllerInterface $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * @inheritdoc
     */
    public function getController(): ControllerInterface
    {
        return $this->controller;
    }
}
