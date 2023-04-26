<?php declare(strict_types=1);

namespace Digua\Attributes\Guardian;

use Digua\Attributes\Guardian;
use Digua\Interfaces\Route as RouteInterface;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestPathAllowed extends Guardian
{
    /**
     * @var string[]
     */
    protected array $paths;

    /**
     * @param string ...$paths
     */
    public function __construct(string ...$paths)
    {
        $this->paths = $paths;
    }

    /**
     * @inheritdoc
     */
    public function granted(RouteInterface $route): bool
    {
        $passedPaths = $route->builder()->request()->getData()->query()->getPathAsList();
        $passedBase  = $route->getBaseName();
        if (isset($passedPaths[$passedBase])) {
            unset($passedPaths[$passedBase]);
        }

        return empty(array_diff(array_keys($passedPaths), $this->paths));
    }
}