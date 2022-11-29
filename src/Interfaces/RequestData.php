<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Request\{Post, Query, Cookies};

interface RequestData
{
    /**
     * @return Post
     */
    public function post(): Post;

    /**
     * @return Query
     */
    public function query(): Query;

    /**
     * @return Cookies
     */
    public function cookies(): Cookies;
}