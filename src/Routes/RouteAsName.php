<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Injector;
use Digua\Providers\Registry as RegistryProvider;
use Digua\Attributes\Guardian as GuardianAttribute;
use Digua\Interfaces\{
    Controller as ControllerInterface,
    Guardian as GuardianInterface,
    Route as RouteInterface,
    Injector as InjectorInterface,
    Route\Builder as RouteBuilderInterface
};
use Digua\Exceptions\{
    Base as BaseException,
    Injector as InjectorException,
    Route as RouteException
};
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionException;

class RouteAsName implements RouteInterface
{
    /**
     * @var ?string
     */
    private ?string $controllerName;

    /**
     * @var ?string
     */
    private ?string $actionName;

    /**
     * @var ?InjectorInterface
     */
    private ?InjectorInterface $injector;

    /**
     * @param string                $appEntryPath
     * @param RouteBuilderInterface $builder
     */
    public function __construct(
        private readonly string $appEntryPath,
        private readonly RouteBuilderInterface $builder
    ) {
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
     * @throws BaseException
     */
    public function isPermitted(ControllerInterface $controller): bool
    {
        if (is_subclass_of($controller, GuardianInterface::class)) {
            return $controller->granted($this);
        }

        // Allowed access to entrypoint controller action
        $paths = $this->builder->request()->getData()->query()->getPathAsList();
        if (sizeof($paths) < 2) {
            return true;
        }

        try {
            $reflection = new ReflectionMethod($controller, $this->getControllerAction());
            $attributes = $reflection->getAttributes(GuardianAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
            if (sizeof($attributes) > 0) {
                foreach ($attributes as $attribute) {
                    /* @var GuardianAttribute $guardian */
                    $guardian = $attribute->newInstance();
                    if (!$guardian->granted($this)) {
                        return false;
                    }
                }

                return true;
            }
        } catch (ReflectionException $e) {
            throw new BaseException($e->getMessage());
        }

        return false;
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

            return $this->getEntryPath() . ucfirst($this->controllerName);
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
        return !empty($this->controllerName)
            ? strtolower(basename(str_replace('\\', '/', $this->controllerName)))
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAction(): ?string
    {
        return !empty($this->actionName)
            ? strtolower(basename(str_replace('::', '/', $this->actionName)))
            : null;
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

    /**
     * @inheritdoc
     */
    public function getEntryPath(): string
    {
        return $this->appEntryPath;
    }

    /**
     * @param InjectorInterface $injector
     * @return void
     */
    public function addInjector(InjectorInterface $injector): void
    {
        $this->injector = $injector;
    }

    /**
     * @param ControllerInterface $controller
     * @return array
     * @throws BaseException
     * @throws RouteException
     */
    public function provide(ControllerInterface $controller): array
    {
        try {
            $injector = $this->injector ?? new Injector($controller, $this->getControllerAction());
            $injector->addProvider(new RouteAsNameProvider($this->builder->request()));
            $injector->addProvider(new RegistryProvider($this->builder->request()));
            return $injector->inject();
        } catch (InjectorException $e) {
            throw new RouteException($e->getMessage(), 400, $e);
        } catch (ReflectionException $e) {
            throw new BaseException($e->getMessage(), 401, $e);
        }
    }
}