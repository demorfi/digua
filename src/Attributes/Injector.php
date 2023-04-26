<?php declare(strict_types=1);

namespace Digua\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Injector
{
    /**
     * @param array $arguments
     */
    public function __construct(private readonly array $arguments)
    {
    }

    /**
     * @param string $name
     * @return ?string
     */
    public function get(string $name): ?string
    {
        return isset($this->arguments[$name]) ? (string)$this->arguments[$name] : null;
    }
}