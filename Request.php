<?php

namespace Digua;

use Digua\Request\{Cookies, Data, Query};

class Request
{
    /**
     * @var Query
     */
    private Query $query;

    /**
     * @var Data
     */
    private Data $data;

    /**
     * @var Cookies
     */
    private Cookies $cookies;

    public function __construct()
    {
        $this->query   = new Query();
        $this->data    = new Data();
        $this->cookies = new Cookies();
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @return Cookies
     */
    public function getCookies(): Cookies
    {
        return $this->cookies;
    }
}
