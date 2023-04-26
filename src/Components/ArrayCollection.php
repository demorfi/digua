<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Traits\Data;

class ArrayCollection
{
    use Data;

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->array = $values;
    }

    /**
     * @param array $values
     * @return static
     */
    public static function make(array $values): static
    {
        return new static($values);
    }
}