<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Components\FileUpload;
use Digua\Traits\Data as DataTrait;
use Digua\Interfaces\Request\{
    FilteredCollection as FilteredCollectionInterface,
    FilteredInput as FilteredInputInterface
};

class Files implements FilteredCollectionInterface
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
        $this->array = $this->collectFileUpload($this->filteredInput->filteredList(FilteredInput::INPUT_FILES));
    }

    /**
     * @inheritdoc
     */
    public function filtered(): FilteredInputInterface
    {
        return $this->filteredInput;
    }

    /**
     * @param array $collect
     * @return int[]
     */
    protected function collectFileUpload(array $collect): array
    {
        return array_map(static fn ($fileInfo) => new FileUpload($fileInfo), $collect);
    }
}