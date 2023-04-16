<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Digua\{Request, Response, RouteDispatcher, Env};
use Digua\Controllers\{
    Base as BaseController,
    Error as ErrorController
};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Exceptions\Route as RouteException;

class TestTrueController extends BaseController
{
    public function trueAction(): bool
    {
        return true;
    }
}

class TestFalseController extends BaseController
{
    public function falseAction(): bool
    {
        return false;
    }
}

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
     * @return void
     * @throws RouteException
     */
    public function testRouteDefault(): void
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

        $response = $dispatcher->default();
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
     * @throws RouteException
     */
    public function testRouteDefaultWithArguments(): void
    {
        $dispatcher = $this
            ->getMockBuilder(RouteDispatcher::class)
            ->setConstructorArgs([$this->request])
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

        define('APP_ENTRY_PATH', '\App\Controllers\Api\\');

        $response = $dispatcher->default($this->builder);
        $this->assertInstanceOf(Response::class, $response);

        [$route, $controller, $action] = array_values($response->getData());

        $this->assertInstanceOf(RouteAsName::class, $route);
        $this->assertInstanceOf(RouteAsNameBuilder::class, $route->builder());
        $this->assertSame(ErrorController::class, $controller);
        $this->assertSame('default', $action);
        $this->assertSame('\App\Controllers\Api\\', $route->getEntryPath());

        $response = $dispatcher->default(null, '\App\Controllers\Other\\');
        $this->assertInstanceOf(Response::class, $response);

        [$route] = array_values($response->getData());
        $this->assertSame('\App\Controllers\Other\\', $route->getEntryPath());
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateUnknownController(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $route      = new RouteAsName('\App\Controllers\\', $this->builder);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('\App\Controllers\Main - controller not found!');
        $dispatcher->delegate($route);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegateUnknownActionController(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $this->builder->forced(TestTrueController::class, 'unknown');
        $route = new RouteAsName('', $this->builder);

        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('TestTrueController->unknownAction - action not found!');
        $dispatcher->delegate($route);
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $this->builder->forced(TestTrueController::class, 'true');

        $route    = new RouteAsName('', $this->builder);
        $response = $dispatcher->delegate($route);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->getData());
        $this->assertInstanceOf(RouteAsName::class, $dispatcher->getRoute());
        $this->assertInstanceOf(RouteAsName::class, $this->request->getRoute());
        $this->assertInstanceOf(TestTrueController::class, $this->request->getController());
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testTrySuccessRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $this->builder->forced(TestTrueController::class, 'true');
        $route = new RouteAsName('', $this->builder);

        Env::prod();
        $response = $dispatcher->try($route, ErrorController::class, 'default');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(TestTrueController::class, $dispatcher->getController());
        $this->assertTrue($response->getData());
    }

    /**
     * @return void
     * @throws RouteException
     */
    public function testTryAlternativeControllerInRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $this->builder->forced(TestTrueController::class, '');
        $route = new RouteAsName('', $this->builder);

        Env::prod();
        $response = $dispatcher->try($route, TestFalseController::class, 'false');
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(TestFalseController::class, $dispatcher->getController());
        $this->assertFalse($response->getData());
    }

    /**
     * @runInSeparateProcess
     * @return void
     * @throws RouteException
     */
    public function testTryFailedRouteDelegate(): void
    {
        $dispatcher = new RouteDispatcher($this->request);
        $this->builder->forced(TestTrueController::class, '');
        $route = new RouteAsName('', $this->builder);

        Env::prod();
        $this->expectException(RouteException::class);
        $this->expectExceptionMessage('Test - controller not found!');
        $dispatcher->try($route, 'Test', '');
    }
}