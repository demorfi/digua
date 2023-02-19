<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Interfaces\RouteBuilder as RouteBuilderInterface;
use Digua\Interfaces\Request as RequestInterface;

class RouteAsNameBuilder implements RouteBuilderInterface
{
    /**
     * @var string|null
     */
    private ?string $controllerName = null;

    /**
     * @var string|null
     */
    private ?string $actionName = null;

    /**
     * @param RequestInterface $request
     */
    public function __construct(private readonly RequestInterface $request)
    {
        // Third argument to check long path
        $variables = $this->request->getData()->query()->getFromPath(1, 2, 3);

        // Disable non-existent paths
        if (empty($variables) || sizeof($variables) < 3) {
            $this->controllerName = $variables[0] ?? 'main';
            $this->actionName     = $variables[1] ?? 'default';
        }
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