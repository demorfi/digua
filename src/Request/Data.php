<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Interfaces\Request\Data as RequestDataInterface;

class Data implements RequestDataInterface
{
    /**
     * @param Post    $post
     * @param Query   $query
     * @param Cookies $cookies
     * @param Files   $files
     */
    public function __construct(
        private readonly Post $post = new Post,
        private readonly Query $query = new Query,
        private readonly Cookies $cookies = new Cookies,
        private readonly Files $files = new Files
    ) {
    }

    /**
     * @return Post
     */
    public function post(): Post
    {
        return $this->post;
    }

    /**
     * @return Query
     */
    public function query(): Query
    {
        return $this->query;
    }

    /**
     * @return Cookies
     */
    public function cookies(): Cookies
    {
        return $this->cookies;
    }

    /**
     * @return Files
     */
    public function files(): Files
    {
        return $this->files;
    }
}
