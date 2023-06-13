<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Interfaces\{
    Request as RequestInterface,
    Route\Builder as RouteBuilderInterface
};

class RouteAsNameBuilder implements RouteBuilderInterface
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
     * @param RequestInterface $request
     */
    public function __construct(private readonly RequestInterface $request)
    {
        $variables = $this->request->getData()->query()->getFromPath(1, 2);

        $this->controllerName = $variables[0] ?? 'main';
        $this->actionName     = $variables[1] ?? 'default';
    }

    /**
     * @inheritdoc
     */
    public function request(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function getControllerName(): ?string
    {
        return $this->controllerName;
    }

    /**
     * @inheritdoc
     */
    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    /**
     * @inheritdoc
     */
    public function forced(string $controllerName, string $actionName): static
    {
        $this->controllerName = $controllerName;
        $this->actionName     = $actionName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function get(string $path, string $action): static
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAction(string $path): string
    {
        return $this->actionName ?? '';
    }
}