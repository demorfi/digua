<?php declare(strict_types=1);

namespace Tests;

use Digua\Interfaces\{
    Controller as ControllerInterface,
    Request\Data as RequestDataInterface,
    Route as RouteInterface
};
use Digua\Request;
use Digua\Exceptions\Route as RouteException;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @return void
     */
    public function testDataObjectIsReturned(): void
    {
        $request = new Request();
        $this->assertInstanceOf(RequestDataInterface::class, $request->getData());
    }

    /**
     * @return void
     */
    public function testRouteObjectIsReturned(): void
    {
        $request = new Request();
        $request->setRoute($this->createMock(RouteInterface::class));
        $this->assertInstanceOf(RouteInterface::class, $request->getRoute());
    }

    /**
     * @return void
     */
    public function testControllerObjectIsReturned(): void
    {
        $request = new Request();
        $request->setController($this->createMock(ControllerInterface::class));
        $this->assertInstanceOf(ControllerInterface::class, $request->getController());
    }

    /**
     * @return void
     */
    public function testAbortExceptionObjectIsReturned(): void
    {
        $request = new Request();
        $request->abort(new RouteException);
        $this->assertInstanceOf(RouteException::class, $request->getException());
    }
}