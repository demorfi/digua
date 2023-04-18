<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Traits\Data as DataTrait;
use Digua\Interfaces\Request\{
    FilteredCollection as FilteredCollectionInterface,
    FilteredInput as FilteredInputInterface
};

class Post implements FilteredCollectionInterface
{
    use DataTrait;

    /**
     * @param FilteredInputInterface $filteredInput
     */
    public function __construct(private readonly FilteredInputInterface $filteredInput = new FilteredInput)
    {
        $this->shake();
    }

    /**
     * @inheritdoc
     */
    public function shake(): void
    {
        $this->array = $this->filteredInput->filteredList(INPUT_POST);
    }

    /**
     * @inheritdoc
     */
    public function filtered(): FilteredInputInterface
    {
        return $this->filteredInput;
    }
}