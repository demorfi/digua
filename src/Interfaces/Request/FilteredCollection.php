<?php declare(strict_types=1);

namespace Digua\Interfaces\Request;

use Digua\Interfaces\NamedCollection as NamedCollectionInterface;

interface FilteredCollection extends NamedCollectionInterface
{
    /**
     * @return FilteredInput
     */
    public function filtered(): FilteredInput;

    /**
     * @return void
     */
    public function shake(): void;
}