<?php declare(strict_types=1);

namespace Tests\Components\Pagination;

use Digua\Components\Pagination\OfArray;
use PHPUnit\Framework\TestCase;
use JsonSerializable;

class OfArrayTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function dataSetProvider(): array
    {
        $list = [];
        for ($i = 0; $i < 100; $i++) {
            $list[] = 'Item ' . $i;
        }

        return [
            '1 page'    => [$list, 100],
            '10 pages'  => [$list, 10],
            '100 pages' => [$list, 1]
        ];
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToNavigation(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        $this->assertSame((int)ceil(sizeof($dataSet) / $limit), $pagination->getTotal());

        $pages = $pagination->getTotal();
        for ($i = 1; $i < $pages; $i++) {
            $this->assertSame($i, $pagination->getCurrent());
            $this->assertTrue($pagination->hasNext());
            $this->assertSame($pagination, $pagination->nextPage());
            $this->assertSame($i + 1, $pagination->getCurrent());
        }

        $this->assertFalse($pagination->hasNext());
        if ($pages > 1) {
            $this->assertTrue($pagination->hasPrev());
        }

        for ($i = $pages; $i > 1; $i--) {
            $this->assertSame($i, $pagination->getCurrent());
            $this->assertTrue($pagination->hasPrev());
            $this->assertSame($pagination, $pagination->prevPage());
            $this->assertSame($i - 1, $pagination->getCurrent());
        }

        $this->assertFalse($pagination->hasPrev());
        if ($pages > 1) {
            $this->assertTrue($pagination->hasNext());
        }
    }

    /**
     * @return void
     */
    public function testIsItPossibleToSetElements(): void
    {
        $pagination = new OfArray(elements: [1, 2, 3], limit: 3);
        $this->assertSame([1, 2, 3], $pagination->getElementsOnPage());
        $this->assertSame([3, 2, 1], $pagination->setElements([3, 2, 1], 3)->getElementsOnPage());
        $this->assertSame([1], $pagination->setElements([1, 2, 3], 1)->getElementsOnPage());
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetElementsOnCurrentPage(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        $page       = $pagination->getTotal();

        $elements = [];
        for ($i = 1; $i <= $page; $i++) {
            $elements = array_merge($elements, $pagination->getElementsOnPage());
            $pagination->nextPage();
        }

        $this->assertSame($dataSet, $elements);
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNextPage(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        $this->assertSame((int)ceil(sizeof($dataSet) / $limit), $pagination->getTotal());

        $pages = $pagination->getTotal();
        for ($i = 1; $i < $pages; $i++) {
            $this->assertSame($i, $pagination->getCurrent());
            $this->assertTrue($pagination->hasNext());
            $this->assertSame((string)($i + 1), $pagination->getNextPage());
            $this->assertSame('/url/page/' . $i + 1, $pagination->getNextPage('/url'));
            $this->assertSame($pagination, $pagination->nextPage());
            $this->assertSame($i + 1, $pagination->getCurrent());
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetPrevPage(array $dataSet, int $limit): void
    {
        $totalPage  = (int)ceil(sizeof($dataSet) / $limit);
        $pagination = new OfArray($totalPage, $dataSet, $limit);
        $this->assertSame($totalPage, $pagination->getTotal());

        $pages = $pagination->getTotal();
        for ($i = $pages; $i > 1; $i--) {
            $this->assertSame($i, $pagination->getCurrent());
            $this->assertTrue($pagination->hasPrev());
            $this->assertSame((string)($i - 1), $pagination->getPrevPage());
            $this->assertSame('/url/page/' . $i - 1, $pagination->getPrevPage('/url'));
            $this->assertSame($pagination, $pagination->prevPage());
            $this->assertSame($i - 1, $pagination->getCurrent());
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetHasPages(array $dataSet, int $limit): void
    {
        $totalPage  = (int)ceil(sizeof($dataSet) / $limit);
        $pagination = new OfArray(elements: $dataSet, limit: $limit);

        if ($totalPage > 1) {
            $this->assertTrue($pagination->hasPages());
        } else {
            $this->assertFalse($pagination->hasPages());
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNavigationGenerator(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        $this->assertSame(['page', 'url', 'active'], array_keys($pagination->getNavigation()->current()));
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNavigationGeneratorPage(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        foreach ($pagination->getNavigation() as $nav) {
            $this->assertThat(
                $nav['page'],
                $this->logicalAnd($this->lessThanOrEqual($pagination->getTotal()), $this->greaterThanOrEqual(1))
            );
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNavigationGeneratorEmptyUrl(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        foreach ($pagination->getNavigation() as $nav) {
            $this->assertSame((string)$nav['page'], $nav['url']);
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNavigationGeneratorUrl(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        foreach ($pagination->getNavigation('/url') as $nav) {
            $this->assertSame('/url/page/' . $nav['page'], $nav['url']);
        }
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetNavigationGeneratorActivePage(array $dataSet, int $limit): void
    {
        $totalPage   = (int)ceil(sizeof($dataSet) / $limit);
        $currentPage = rand(1, $totalPage);
        $pagination  = new OfArray($currentPage, $dataSet, $limit);

        $active = [];
        foreach ($pagination->getNavigation() as $nav) {
            if ($nav['active']) {
                $active[] = $nav['page'];
            }
        }

        $this->assertSame([$currentPage], $active);
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @param int   $limit
     * @return void
     */
    public function testIsItPossibleToGetJsonSerialize(array $dataSet, int $limit): void
    {
        $pagination = new OfArray(elements: $dataSet, limit: $limit);
        $this->assertInstanceOf(JsonSerializable::class, $pagination);
        $pages = (int)ceil(sizeof($dataSet) / $limit);
        for ($i = 1; $i < $pages; $i++) {
            $this->assertSame(
                [
                    'hasPages' => $pagination->hasPages(),
                    'hasNext'  => $pagination->hasNext(),
                    'hasPrev'  => $pagination->hasPrev(),
                    'total'    => $pagination->getTotal(),
                    'current'  => $pagination->getCurrent(),
                    'nextPage' => $pagination->getNextPage(''),
                    'prevPage' => $pagination->getPrevPage('')
                ],
                $pagination->jsonSerialize()
            );
            $pagination->nextPage();
        }
    }
}