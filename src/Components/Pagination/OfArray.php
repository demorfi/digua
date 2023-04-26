<?php declare(strict_types=1);

namespace Digua\Components\Pagination;

use Digua\Interfaces\Request;
use JsonSerializable;
use Generator;

class OfArray implements JsonSerializable
{
    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * Total elements.
     *
     * @var int
     */
    protected int $total = 1;

    /**
     * @param int   $currentPage
     * @param array $elements
     * @param int   $limit
     */
    public function __construct(
        protected int $currentPage = 1,
        protected array $elements = [],
        protected int $limit = 1
    ) {
        $this->setElements($this->elements, $this->limit);
    }

    /**
     * @param array $elements
     * @param int   $limit
     * @return self
     */
    public function setElements(array $elements, int $limit): self
    {
        $this->elements = $elements;
        $this->limit    = $limit;

        $this->calculate();
        return $this;
    }

    /**
     * @return void
     */
    protected function calculate(): void
    {
        $this->total  = (int)ceil(sizeof($this->elements) / ($this->limit ?: 1));
        $this->offset = ($this->currentPage > 1 ? ($this->currentPage - 1) * $this->limit : 0);
    }

    /**
     * Get elements on current page.
     *
     * @return array
     */
    public function getElementsOnPage(): array
    {
        return array_slice($this->elements, $this->offset, $this->limit);
    }

    /**
     * Has next page.
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->total;
    }

    /**
     * @return self
     */
    public function nextPage(): self
    {
        $this->currentPage += $this->hasNext() ? 1 : 0;
        $this->calculate();
        return $this;
    }

    /**
     * Has prev page.
     *
     * @return bool
     */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1 && $this->currentPage <= $this->total;
    }

    /**
     * @return self
     */
    public function prevPage(): self
    {
        $this->currentPage -= $this->hasPrev() ? 1 : 0;
        $this->calculate();
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPages(): bool
    {
        return $this->total > 1;
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->currentPage;
    }

    /**
     * Get total pages.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param ?string $url Prepend url path before /page/
     * @return string
     */
    public function getNextPage(string $url = null): string
    {
        return (!is_null($url) ? $url . '/page/' : '')
            . ($this->hasNext() ? $this->currentPage + 1 : $this->currentPage);
    }

    /**
     * @param ?string $url Prepend url path before /page/
     * @return string
     */
    public function getPrevPage(string $url = null): string
    {
        return (!is_null($url) ? $url . '/page/' : '')
            . ($this->hasPrev() ? $this->currentPage - 1 : $this->currentPage);
    }

    /**
     * Get generation navigation list.
     *
     * @param ?string $url Prepend url path before /page/
     * @return Generator
     */
    public function getNavigation(string $url = null): Generator
    {
        for ($i = 1; $i <= $this->total; $i++) {
            yield [
                'page'   => $i,
                'url'    => (!is_null($url) ? $url . '/page/' : '') . $i,
                'active' => $i === $this->currentPage
            ];
        }
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'hasPages' => $this->hasPages(),
            'hasNext'  => $this->hasNext(),
            'hasPrev'  => $this->hasPrev(),
            'total'    => $this->getTotal(),
            'current'  => $this->getCurrent(),
            'nextPage' => $this->getNextPage(''),
            'prevPage' => $this->getPrevPage('')
        ];
    }
}
