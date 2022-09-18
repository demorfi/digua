<?php

namespace Digua\Components\Pagination;

use Digua\Request;

class OfArray implements \JsonSerializable
{
    /**
     * Data.
     *
     * @var array
     */
    protected array $array = [];

    /**
     * Offset.
     *
     * @var int
     */
    protected int $offset = 0;

    /**
     * Limit.
     *
     * @var int
     */
    protected int $limit = 0;

    /**
     * Total elements.
     *
     * @var int
     */
    protected int $total = 1;

    /**
     * Current element.
     *
     * @var int
     */
    protected int $current = 1;

    /**
     * Request instance.
     *
     * @var Request
     */
    private Request $request;

    /**
     * PaginationArray constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Set elements for create pagination.
     *
     * @param array $array Elements
     * @param int   $limit
     * @return self
     */
    public function setElements(array $array, int $limit): self
    {
        $page = $this->request->getQuery()->get('page');

        $this->array   = $array;
        $this->limit   = $limit;
        $this->total   = ceil(sizeof($array) / ($limit ?: 1));
        $this->current = (!$page ? 1 : $page);
        $this->offset  = ($this->current > 1 ? ($this->current - 1) * $this->limit : 0);

        return ($this);
    }

    /**
     * Get elements on current page.
     *
     * @return array
     */
    public function getElementsOnPage(): array
    {
        return (array_slice($this->array, $this->offset, $this->limit));
    }

    /**
     * Has next page.
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        return ($this->current < $this->total);
    }

    /**
     * Has prev page.
     *
     * @return bool
     */
    public function hasPrev(): bool
    {
        return ($this->current > 1 && $this->current <= $this->total);
    }

    /**
     * Has pages.
     *
     * @return bool
     */
    public function hasPages(): bool
    {
        return ($this->total > 1);
    }

    /**
     * Get current page.
     *
     * @return int
     */
    public function getCurrent(): int
    {
        return ($this->current);
    }

    /**
     * Get total pages.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return ($this->total);
    }

    /**
     * Get next page.
     *
     * @param string|null $url
     * @return string
     */
    public function getNextPage(string $url = null): string
    {
        return ((!is_null($url) ? $url . '/page/' : '') . ($this->hasNext() ? $this->current + 1 : $this->current));
    }

    /**
     * Get prev page.
     *
     * @param string|null $url
     * @return string
     */
    public function getPrevPage(string $url = null): string
    {
        return ((!is_null($url) ? $url . '/page/' : '') . ($this->hasPrev() ? $this->current - 1 : $this->current));
    }

    /**
     * Get navigation list.
     *
     * @param string|null $url
     * @return \Generator
     */
    public function getNavigation(string $url = null): \Generator
    {
        for ($i = 1; $i <= $this->total; $i++) {
            yield [
                'page'   => $i,
                'url'    => (!empty($url) ? $url . '/page/' : '') . $i,
                'active' => $i == $this->current
            ];
        }
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ([
            'hasPages' => $this->hasPages(),
            'hasNext'  => $this->hasNext(),
            'hasPrev'  => $this->hasPrev(),
            'total'    => $this->getTotal(),
            'current'  => $this->getCurrent(),
            'nextPage' => $this->getNextPage(''),
            'prevPage' => $this->getPrevPage('')
        ]);
    }
}
