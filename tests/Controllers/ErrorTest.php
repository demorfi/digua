<?php declare(strict_types=1);

namespace Tests\Controllers;

use Digua\{Request, Response, Template};
use Digua\Controllers\{Base, Error};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Interfaces\Guardian as GuardianInterface;
use Digua\Exceptions\Path;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @var Error
     */
    private Error $controller;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        $template         = $this->getMockBuilder(Template::class)->disableOriginalConstructor()->getMock();
        $this->controller = $this->getMockForAbstractClass(Error::class, [new Request], mockedMethods: ['render']);
        $this->controller->method('render')->willReturn($template);
    }

    /**
     * @return void
     */
    public function testInstanceOfInterfaces(): void
    {
        $this->assertInstanceOf(Base::class, $this->controller);
        $this->assertInstanceOf(GuardianInterface::class, $this->controller);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGrantedReturnSuccess(): void
    {
        $route = new RouteAsName('', new RouteAsNameBuilder(new Request));
        $this->assertTrue($this->controller->granted($route));
    }

    /**
     * @return void
     * @throws Path
     */
    public function testDefaultActionIsTemplate(): void
    {
        $result = $this->controller->defaultAction();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertInstanceOf(Template::class, $result->getData());
        $this->assertSame([
            'content-type' => ['Content-Type', 'text/html; charset=UTF-8', 0],
            'http'         => ['http', 'HTTP/1.1 404 Not Found', 404]
        ], $result->getHeaders());
    }

    /**
     * @return void
     * @throws Path
     */
    public function testDefaultActionIsAsyncArray(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->controller->dataRequest()->query()->filtered()->refresh(INPUT_SERVER);

        $result = $this->controller->defaultAction();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame(['error' => 'not found'], $result->getData());
        $this->assertSame([
            'content-type' => ['Content-Type', 'application/json; charset=UTF-8', 0],
            'http'         => ['http', 'HTTP/1.1 404 Not Found', 404]
        ], $result->getHeaders());
    }
}