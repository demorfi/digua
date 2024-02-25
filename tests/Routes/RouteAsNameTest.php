<?php declare(strict_types=1);

namespace Tests\Routes;

use Digua\{Request, Registry, Injector};
use Digua\Request\{FilteredInput, Query, Data};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Interfaces\{
    GuardianController as GuardianControllerInterface,
    Controller as ControllerInterface,
    Provider as ProviderInterface
};
use Digua\Exceptions\{
    Base as BaseException,
    Route as RouteException
};
use PHPUnit\Framework\TestCase;
use Tests\Pacifiers\{ControllerSuccess, ControllerFailure, GuardianAttribute, StubService};
use ReflectionException;

class RouteAsNameTest extends TestCase
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var RouteAsName
     */
    private RouteAsName $route;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredList'])
            ->getMock();

        $input->method('filteredList')->willReturnCallback(fn() => $this->data);
        $builder     = new RouteAsNameBuilder(new Request(new Data(query: new Query($input))));
        $this->route = new RouteAsName('\App\Controllers\\', $builder);
        Registry::set(StubService::class, new StubService);
    }

    /**
     * @return void
     */
    public function testRouteIsReturnedBuilder(): void
    {
        $this->assertInstanceOf(RouteAsNameBuilder::class, $this->route->builder());
    }

    /**
     * @return void
     */
    public function testSwitchRoute(): void
    {
        $this->route->switch('Main', 'test');
        $this->assertSame('\App\Controllers\Main', $this->route->getControllerName());
        $this->assertSame('testAction', $this->route->getControllerAction());

        $this->route->switch('\App\Controllers\Api\Main', 'def');
        $this->assertSame('\App\Controllers\Api\Main', $this->route->getControllerName());
        $this->assertSame('defAction', $this->route->getControllerAction());
    }

    /**
     * @return void
     */
    public function testControllerName(): void
    {
        $this->assertSame('\App\Controllers\Main', $this->route->getControllerName());
    }

    /**
     * @return void
     */
    public function testControllerAction(): void
    {
        $this->assertSame('defaultAction', $this->route->getControllerAction());
    }

    /**
     * @return void
     */
    public function testBaseName(): void
    {
        $this->assertSame('main', $this->route->getBaseName());
    }

    /**
     * @return void
     */
    public function testBaseAction(): void
    {
        $this->assertSame('default', $this->route->getBaseAction());
    }

    /**
     * @return void
     */
    public function testBasePath(): void
    {
        $this->assertSame('main.default', $this->route->getBasePath());
    }

    /**
     * @return void
     */
    public function testEntryPath(): void
    {
        $this->assertSame('\App\Controllers\\', $this->route->getEntryPath());
    }

    /**
     * @return void
     */
    public function testHasRoute(): void
    {
        $this->assertTrue($this->route->hasRoute('main.default'));
        $this->assertFalse($this->route->hasRoute('default.main'));
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testPermittedGuardianInterface(): void
    {
        $controller = $this->getMockBuilder(GuardianControllerInterface::class)
            ->onlyMethods(['granted', 'getName'])
            ->getMock();

        $controller->expects($this->once())->method('granted')->willReturn(true);
        $this->assertTrue($this->route->isPermitted($controller));
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testPermittedAccessToEntrypoint(): void
    {
        $controller = $this->getMockBuilder(ControllerInterface::class)
            ->onlyMethods(['getName'])
            ->addMethods(['actionAction'])
            ->getMock();

        $this->data = ['REQUEST_URI' => '/controller/action'];
        $this->setUp();
        $this->assertTrue($this->route->isPermitted($controller));

        $this->data = ['REQUEST_URI' => '/controller/action/test/page'];
        $this->setUp();
        $this->assertFalse($this->route->isPermitted($controller));
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testPermittedViaAttributes(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/success/test/page'];
        $this->setUp();
        $this->assertTrue($this->route->isPermitted(new ControllerSuccess(new Request)));

        $this->data = ['REQUEST_URI' => '/controller/failure/test/page'];
        $this->setUp();
        $this->assertFalse($this->route->isPermitted(new ControllerFailure(new Request)));

        $this->assertSame(2, GuardianAttribute::$called);
    }

    /**
     * @return void
     * @throws BaseException
     */
    public function testPermittedIfControllerOrActionIsNotFound(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/success/test/page'];
        $this->setUp();
        $this->expectException(BaseException::class);

        $controller = $this->getMockBuilder(ControllerInterface::class)
            ->onlyMethods(['getName'])
            ->addMethods(['actionAction'])
            ->getMock();

        $this->route->isPermitted($controller);
    }

    /**
     * @return void
     * @throws BaseException
     * @throws RouteException
     */
    public function testIsItPossibleToGetInjectInProvide(): void
    {
        $controller = $this->getMockBuilder(ControllerSuccess::class)
            ->setConstructorArgs([new Request])
            ->getMock();

        $injector = $this->getMockBuilder(Injector::class)
            ->setConstructorArgs([$controller, 'successAction'])
            ->getMock();

        $this->route->addInjector($injector);

        $injector->expects($this->exactly(3))
            ->method('addProvider')
            ->with($this->containsOnlyInstancesOf(ProviderInterface::class));

        $injector->expects($this->once())
            ->method('inject')
            ->willReturn([1, 2, 3]);

        $this->assertSame([1, 2, 3], $this->route->provide($controller));
    }
}