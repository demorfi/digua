<?php declare(strict_types=1);

namespace Tests\Request;

use Digua\Request\{Data, Cookies, Post, Query};
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private Data $data;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->data = new Data;
    }

    /**
     * @return void
     */
    public function testPostObjectIsReturned(): void
    {
        $this->assertInstanceOf(Post::class, $this->data->post());
    }

    /**
     * @return void
     */
    public function testQueryObjectIsReturned(): void
    {
        $this->assertInstanceOf(Query::class, $this->data->query());
    }

    /**
     * @return void
     */
    public function testCookiesObjectIsReturned(): void
    {
        $this->assertInstanceOf(Cookies::class, $this->data->cookies());
    }
}