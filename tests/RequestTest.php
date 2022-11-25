<?php

use Digua\Request;
use Digua\Request\{Data, Query, Cookies};
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testDataObjectIsReturned()
    {
        $request = new Request();
        $this->assertInstanceOf(Data::class, $request->getData());
    }

    public function testQueryObjectIsReturned()
    {
        $request = new Request();
        $this->assertInstanceOf(Query::class, $request->getQuery());
    }

    public function testCookiesObjectIsReturned()
    {
        $request = new Request();
        $this->assertInstanceOf(Cookies::class, $request->getCookies());
    }
}