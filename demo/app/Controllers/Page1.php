<?php declare(strict_types=1);

namespace App\Controllers;

use Digua\{Config, Request, Template, Helper};
use Digua\Exceptions\Path as PathException;
use Digua\Controllers\Base as BaseController;
use Digua\Components\Pagination\OfArray as PaginationOfArray;
use Digua\Components\Searching\InArray as SearchInArray;

class Page1 extends BaseController
{
    /**
     * Pagination elements limit on page.
     *
     * @var int
     */
    const PAGINATION_LIMITS = 2;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Hosts constructor.
     *
     * @param Request $request
     * @throws PathException
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->config = Helper::config('base');
    }

    /**
     * Default action.
     *
     * @return Template
     * @throws PathException
     */
    public function defaultAction(): Template
    {
        $title      = 'Page 1 [debug is ' . ($this->config->get('debug') ? 'active' : 'inactive') . ']';
        $pagination = new PaginationOfArray($this->request);
        return $this->render('page1', compact('title', 'pagination'));
    }

    /**
     * Action 1 list.
     *
     * @return Template|array
     * @throws PathException
     */
    public function list1Action(): Template|array
    {
        return $this->listAction('List 1');
    }

    /**
     * Action 2 list.
     *
     * @return Template|array
     * @throws PathException
     */
    public function list2Action(): Template|array
    {
        return $this->listAction('List 2');
    }

    /**
     * @param string $title
     * @return Template|array
     * @throws PathException
     */
    private function listAction(string $title): Template|array
    {
        $list = [];
        for ($i = 1; $i <= self::PAGINATION_LIMITS * 5; $i++) {
            $list[] = [
                'id'   => $i,
                'name' => $title . ' of Item ' . $i
            ];
        }

        // Search
        if ($this->dataRequest()->post()->has('search')) {
            $search = new SearchInArray($list);
            $list   = $search->find('name', $this->dataRequest()->post()->get('name'));
        }

        // Pagination list
        $pagination = (new PaginationOfArray($this->request))->setElements($list, self::PAGINATION_LIMITS);
        $list       = $pagination->getElementsOnPage();

        // Return json data
        if ($this->dataRequest()->query()->isAsync()) {
            $data = $list;
            return compact('title', 'data', 'pagination');
        }

        return $this->render('page1', compact('title', 'list', 'pagination'));
    }
}
