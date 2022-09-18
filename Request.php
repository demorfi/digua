<?php

namespace Digua;

use Digua\Request\Cookies;
use Digua\Request\Data;
use Digua\Request\Query;

class Request
{
    /**
     * Query instance.
     *
     * @var Query
     */
    private Query $query;

    /**
     * Data instance.
     *
     * @var Data
     */
    private Data $data;

    /**
     * @var Cookies
     */
    private Cookies $cookies;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->query   = new Query();
        $this->data    = new Data();
        $this->cookies = new Cookies();
    }

    /**
     * Get data instance.
     *
     * @return Data
     */
    public function getData(): Data
    {
        return ($this->data);
    }

    /**
     * Get query instance.
     *
     * @return Query
     */
    public function getQuery(): Query
    {
        return ($this->query);
    }

    /**
     * Get cookies instance.
     *
     * @return Cookies
     */
    public function getCookies(): Cookies
    {
        return ($this->cookies);
    }
}
