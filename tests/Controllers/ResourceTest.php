<?php declare(strict_types=1);

namespace Tests\Controllers;

use Digua\Request;
use Digua\Request\{Data, FilteredInput, Query};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Controllers\{Resource, Base};
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class ResourceTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function methodsProvider(): array
    {
        return [
            'get'  => ['GET'],
            'post' => ['POST'],
            'put'  => ['PUT'],
            'path' => ['PATH']
        ];
    }

    /**
     * @param string $resMethod
     * @return RouteAsName|MockObject
     */
    protected function getRouteAsName(string $resMethod): RouteAsName|MockObject
    {
        $input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredVar'])
            ->getMock();
        $input->method('filteredVar')->willReturn($resMethod);

        $request = new Request(new Data(query: new Query($input)));
        $builder = $this->getMockBuilder(RouteAsNameBuilder::class)
            ->setConstructorArgs([$request])
            ->onlyMethods(['getControllerName', 'getActionName'])
            ->getMock();

        $builder->method('getControllerName')->willReturn('main');
        $builder->method('getActionName')->willReturn('some');

        $route = $this->getMockBuilder(RouteAsName::class)
            ->setConstructorArgs(['\App\Controllers\\', $builder])
            ->onlyMethods(['switch'])
            ->getMock();

        $request->setRoute($route);
        return $route;
    }

    /**
     * @return void
     */
    public function testInstanceOfInterface(): void
    {
        $resource = $this->getMockBuilder(Resource::class)
            ->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(Base::class, $resource);
    }

    /**
     * @return void
     */
    public function testWhetherResourceMethodIsDefined(): void
    {
        $route      = $this->getRouteAsName('test string');
        $resource   = new Resource($route->builder()->request());
        $reflection = new ReflectionClass($resource);
        $property   = $reflection->getProperty('method');
        $this->assertSame('test string', $property->getValue($resource));
    }

    /**
     * @dataProvider methodsProvider
     * @param string $method
     * @return void
     */
    public function testSuccessSwitchResAction(string $method): void
    {
        $route = $this->getRouteAsName($method);
        $route->expects($this->once())->method('switch')
            ->with('main', strtolower($method) . 'Some');

        $this->getMockBuilder(Resource::class)
            ->setConstructorArgs([$route->builder()->request()])
            ->addMethods([strtolower($method) . 'SomeAction'])
            ->getMock();
    }

    /**
     * @dataProvider methodsProvider
     * @param string $method
     * @return void
     */
    public function testNeverSwitchResAction(string $method): void
    {
        $route = $this->getRouteAsName($method);
        $route->expects($this->never())->method('switch');

        $this->getMockBuilder(Resource::class)
            ->setConstructorArgs([$route->builder()->request()])
            ->getMock();
    }
}