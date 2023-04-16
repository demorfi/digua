<?php declare(strict_types=1);

namespace Routes;

use PHPUnit\Framework\TestCase;
use Digua\Request;
use Digua\Routes\RouteAsNameBuilder;

class RouteAsNameBuilderTest extends TestCase
{
    /**
     * @var RouteAsNameBuilder
     */
    private RouteAsNameBuilder $builder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->builder = new RouteAsNameBuilder(new Request);
    }

    /**
     * @return void
     */
    public function testBuilderIsReturnedRequest(): void
    {
        $this->assertInstanceOf(Request::class, $this->builder->request());
    }

    /**
     * @return void
     */
    public function testControllerName(): void
    {
        $this->assertSame('main', $this->builder->getControllerName());
    }

    /**
     * @return void
     */
    public function testActionName(): void
    {
        $this->assertSame('default', $this->builder->getActionName());
    }

    /**
     * @return void
     */
    public function testBuilderDefinedPath(): void
    {
        $request = new Request;
        $request->getData()->query()->buildPath('controller', 'action');
        $builder = new RouteAsNameBuilder($request);

        $this->assertSame('controller', $builder->getControllerName());
        $this->assertSame('action', $builder->getActionName());
    }

    /**
     * @return void
     */
    public function testBuilderForcedControllerAndAction(): void
    {
        $builder = $this->builder->forced('TestController', 'TestAction');
        $this->assertInstanceOf(RouteAsNameBuilder::class, $builder);
        $this->assertSame('TestController', $builder->getControllerName());
        $this->assertSame('TestAction', $builder->getActionName());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToCreateAction(): void
    {
        $this->assertInstanceOf(RouteAsNameBuilder::class, $this->builder->get('path', 'action'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetAction(): void
    {
        $this->assertSame('default', $this->builder->getAction('path'));
    }
}