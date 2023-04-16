<?php declare(strict_types=1);

namespace Routes;

use PHPUnit\Framework\TestCase;
use Digua\Request;
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};

class RouteAsNameTest extends TestCase
{
    /**
     * @var RouteAsName
     */
    private RouteAsName $route;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $builder = new RouteAsNameBuilder(new Request);
        $this->route = new RouteAsName('\App\Controllers\\', $builder);
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
}