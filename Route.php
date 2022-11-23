<?php declare(strict_types = 1);

namespace Digua;

use Digua\Exceptions\{
    Loader as LoaderException,
    Route as RouteException
};
use Digua\Interfaces\Route as RouteInterface;
use Digua\Controllers\{
    Base as BaseController,
    Error as ErrorController
};

class Route implements RouteInterface
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var BaseController|string
     */
    protected BaseController|string $name;

    /**
     * @var string
     */
    protected string $action;

    /**
     * @var bool
     */
    protected bool $accessible;

    /**
     * @param BaseController|string|null $defName
     * @param ?string|null     $defAction
     */
    public function __construct(BaseController|string|null $defName = null, ?string $defAction = null)
    {
        $this->request    = new Request();
        $this->name       = $defName ?: $this->request->getQuery()->getName();
        $this->action     = $defAction ?: $this->request->getQuery()->getAction();
        $this->accessible = !empty($defName);
    }

    /**
     * @inheritdoc
     * @throws RouteException
     */
    public function delegate(?RouteInterface $route = null): Response
    {
        if ($route instanceof RouteInterface) {
            return $route->delegate($route);
        }

        $controller = $this->name;
        $action     = ($this->action . 'Action');

        if (is_string($controller)) {
            $name = str_contains($this->name, '\\')
                ? $this->name
                : ('\App\Controllers\\' . ucfirst($this->name));

            try {
                if (!class_exists($name)) {
                    throw new RouteException($name . ' - controller not found!');
                }
            } catch (LoaderException) {
                throw new RouteException($name . ' - controller not found!');
            }

            if (!is_subclass_of($name, BaseController::class)) {
                throw new RouteException($name . ' - controller not implemented!');
            }

            $controller = new $name($this->request);
        }

        if (!$this->accessible && !$controller->accessible) {
            throw new RouteException($controller::class . ' - controller not accessible!');
        }

        if (!method_exists($controller, $action)) {
            throw new RouteException($controller::class . '->' . $action . ' - action not found!');
        }

        return Response::create(call_user_func([$controller, $action]))->build();
    }

    /**
     * @param ErrorController|string|null $error
     * @return Response
     * @throws RouteException
     */
    public function try(ErrorController|string|null $error = null): Response
    {
        try {
            return $this->delegate();
        } catch (RouteException $e) {
            if (!empty($error)) {
                return (new self($error, 'default'))->delegate();
            }

            header('HTTP/1.1 500 Internal Server Error');
            throw new RouteException($e->getMessage());
        }
    }
}
