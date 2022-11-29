<?php declare(strict_types=1);

use Digua\Interfaces\{
    Controller as ControllerInterface,
    RequestData as RequestDataInterface,
    Route as RouteInterface
};
use Digua\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testDataObjectIsReturned()
    {
        $request = new Request();
        $this->assertInstanceOf(RequestDataInterface::class, $request->getData());
    }

    public function testRouteObjectIsReturned()
    {
        $request = new Request();
        $request->setRoute($this->createMock(RouteInterface::class));
        $this->assertInstanceOf(RouteInterface::class, $request->getRoute());
    }

    public function testControllerObjectIsReturned()
    {
        $request = new Request();
        $request->setController($this->createMock(ControllerInterface::class));
        $this->assertInstanceOf(ControllerInterface::class, $request->getController());
    }
}