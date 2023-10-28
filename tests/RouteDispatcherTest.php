<?php declare(strict_types=1);

namespace Tests;

use Digua\{Request, Response, RouteDispatcher};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Controllers\Error as ErrorController;
use Digua\Interfaces\{
    Controller as ControllerInterface,
    Route as RouteInterface
};
use Digua\Exceptions\{
    Route as RouteException,
    Base as BaseException
};
use PHPUnit\Framework\TestCase;
use Tests\Pacifiers\{ControllerSuccess, ControllerFailure, ControllerAssets};

class RouteDispatcherTest extends TestCase
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var RouteAsNameBuilder
     */
    private RouteAsNameBuilder $builder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->request = new Request;
        $this->builder = new RouteAsNameBuilder($this->request);
    }

    /**
     * @return RouteDispatcher
     */
    protected function getMockTryMethod(): RouteDispatcher
    {
        $dispatcher = $this
            ->getMockBuilder(RouteDispatcher::class)
            ->onlyMethods(['try'])
            ->getMock();

        $dispatcher->method('try')
            ->will(
                $this->returnCallback(
                    function (mixed $route, string $controller, string $action) {
                        return Response::create(compact('route', 'controller', 'action'));
                    }
                )
            );

        return $dispatcher;
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteDefault(): void
    {
        $response = $this->getMockTryMethod()->default();
        $this->assertInstanceOf(Response::class, $response);

        [$route, $controller, $action] = array_values($response->getData());

        $this->assertInstanceOf(RouteAsName::class, $route);
        $this->assertInstanceOf(RouteAsNameBuilder::class, $route->builder());
        $this->assertSame(ErrorController::class, $controller);
        $this->assertSame('default', $action);
        $this->assertSame('\App\Controllers\\', $route->getEntryPath());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteDefaultWithBuilder(): void
    {
        $response = $this->getMockTryMethod()->default(new RouteAsNameBuilder($this->request));
        $this->assertInstanceOf(Response::class, $response);

        [$route] = array_values($response->getData());

        $this->assertInstanceOf(RouteAsName::class, $route);
        $this->assertInstanceOf(RouteAsNameBuilder::class, $route->builder());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteDefaultWithEntryPath(): void
    {
        $response = $this->getMockTryMethod()->default(null, '\App\Controllers\Other\\');
        $this->assertInstanceOf(Response::class, $response);

        [$route] = array_values($response->getData());
        $this->assertSame('\App\Controllers\Other\\', $route->getEntryPath());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteDefaultWithDefinedEntryPath(): void
    {
        define('APP_ENTRY_PATH', '\App\Controllers\Api\\');

        $response = $this->getMockTryMethod()->default();
        $this->assertInstanceOf(Response::class, $response);

        [$route] = array_values($response->getData());
        $this->assertSame('\App\Controllers\Api\\', $route->getEntryPath());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteDefaultWithErrorController(): void
    {
        $response = $this->getMockTryMethod()->default(errorController: ControllerFailure::class);
        $this->assertInstanceOf(Response::class, $response);

        [, $controller, $action] = array_values($response->getData());
        $this->assertSame(ControllerFailure::class, $controller);
        $this->assertSame('default', $action);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateUnknownController(): void
    {
        $dispatcher = new RouteDispatcher();
        $route      = new RouteAsName('\App\Controllers\\', $this->builder);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('\App\Controllers\Main - controller not found!');
        $this->expectExceptionCode(100);
        $dispatcher->delegate($route);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateUnknownActionController(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'unknown');
        $route = new RouteAsName('', $this->builder);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('ControllerSuccess->unknownAction - action not found!');
        $this->expectExceptionCode(200);
        $dispatcher->delegate($route);
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testTrySuccessRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'success');
        $route = new RouteAsName('', $this->builder);

        $response = $dispatcher->try($route, ErrorController::class, 'default');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ControllerSuccess::class, $dispatcher->getController());
        $this->assertTrue($response->getData());
    }

    /**
     * @runInSeparateProcess
     * @return void
     */
    public function testTryFailedAlternativeControllerInRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerFailure::class, '');
        $route = new RouteAsName('', $this->builder);

        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('Test - controller not found!');
        $dispatcher->try($route, 'Test', '');
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testTryAlternativeControllerInRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, '');
        $route = new RouteAsName('', $this->builder);

        $response = $dispatcher->try($route, ControllerFailure::class, 'failure');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(ControllerFailure::class, $dispatcher->getController());
        $this->assertFalse($response->getData());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testRouteObjectIsReturned(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'success');
        $route = new RouteAsName('', $this->builder);

        $dispatcher->try($route, ControllerFailure::class, 'failure');
        $this->assertInstanceOf(RouteInterface::class, $dispatcher->getRoute());
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testControllerObjectIsReturned(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'success');
        $route = new RouteAsName('', $this->builder);

        $dispatcher->try($route, ControllerFailure::class, 'failure');
        $this->assertInstanceOf(ControllerInterface::class, $dispatcher->getController());
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegatePermitted(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'success');

        $route = $this->getMockBuilder(RouteAsName::class)
            ->setConstructorArgs(['', $this->builder])
            ->onlyMethods(['isPermitted'])
            ->getMock();

        $route->expects($this->once())->method('isPermitted')->willReturn(true);
        $response = $dispatcher->delegate($route);
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateNotPermitted(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerSuccess::class, 'success');

        $route = $this->getMockBuilder(RouteAsName::class)
            ->setConstructorArgs(['', $this->builder])
            ->onlyMethods(['isPermitted'])
            ->getMock();

        $route->expects($this->once())->method('isPermitted')->willReturn(false);
        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('ControllerSuccess->successAction - access not granted!');
        $this->expectExceptionCode(300);
        $dispatcher->delegate($route);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateSetsRequest(): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['setRoute', 'setController'])
            ->getMock();

        $request->expects($this->once())->method('setRoute')
            ->with($this->containsOnlyInstancesOf(RouteInterface::class));
        $request->expects($this->once())->method('setController')
            ->with($this->containsOnlyInstancesOf(ControllerInterface::class));

        $builder = new RouteAsNameBuilder($request);
        $builder->forced(ControllerSuccess::class, 'success');

        $dispatcher = new RouteDispatcher();
        $dispatcher->delegate(new RouteAsName('', $builder));
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateProvide(): void
    {
        $dispatcher = new RouteDispatcher();
        $this->builder->forced(ControllerAssets::class, 'assets');

        $route = $this->getMockBuilder(RouteAsName::class)
            ->setConstructorArgs(['', $this->builder])
            ->onlyMethods(['provide'])
            ->getMock();

        $route->expects($this->once())->method('provide')->willReturn([1, 2, 3]);
        $response = $dispatcher->delegate($route);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame([1, 2, 3], $response->getData());
    }
}