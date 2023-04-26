<?php declare(strict_types=1);

namespace Digua\Attributes\Guardian;

use Digua\Interfaces\Route as RouteInterface;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestPathRequired extends RequestPathAllowed
{
    /**
     * @inheritdoc
     */
    public function granted(RouteInterface $route): bool
    {
        $passedPaths = $route->builder()->request()->getData()->query()->getPathAsList();
        return empty(array_diff($this->paths, array_keys($passedPaths)));
    }
}