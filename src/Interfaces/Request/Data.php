<?php declare(strict_types=1);

namespace Digua\Interfaces\Request;

use Digua\Request\{Cookies, Post, Query, Files};

interface Data
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

    /**
     * @return Files
     */
    public function files(): Files;
}