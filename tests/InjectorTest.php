<?php declare(strict_types=1);

namespace Tests;

use Digua\Injector;
use Digua\Interfaces\Provider as ProviderInterface;
use Digua\Exceptions\Injector as InjectorException;
use PHPUnit\Framework\TestCase;
use Tests\Pacifiers\{ControllerAssets, StubService};
use ReflectionException;

class InjectorTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function assetsControllerProvider(): array
    {
        $object = $this->getMockBuilder(ControllerAssets::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(ProviderInterface::class)
            ->onlyMethods(['hasType', 'get'])
            ->getMock();

        $value   = rand(PHP_INT_MIN, PHP_INT_MAX);
        $service = new StubService;
        $map     = [
            ['key', 'int', $value],
            ['value', 'string', 'test string'],
            ['service', StubService::class, $service]
        ];

        $provider->method('hasType')->willReturn(true);
        $provider->method('get')->willReturnMap($map);

        return [
            'assetsIntAction'       => [$object, 'assetsIntAction', $provider, [$value]],
            'assetsIntStringAction' => [$object, 'assetsIntStringAction', $provider, [$value, 'test string']],
            'assetsStubAction'      => [$object, 'assetsStubAction', $provider, [$service]],
            'assetsStubMixedAction' => [$object, 'assetsStubMixedAction', $provider, [$value, $service]]
        ];
    }

    /**
     * @dataProvider assetsControllerProvider
     * @param object $object
     * @param string $method
     * @param mixed  $provider
     * @param mixed  $expect
     * @return void
     * @throws InjectorException
     * @throws ReflectionException
     */
    public function testInject(object $object, string $method, mixed $provider, mixed $expect): void
    {
        $injector = new Injector($object, $method);
        $injector->addProvider($provider);

        $provider->expects($this->any())->method('hasType');
        $provider->expects($this->any())->method('get');

        $this->assertSame($expect, $injector->inject());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testThrowNotFoundMethodInject(): void
    {
        $object = $this->getMockBuilder(ControllerAssets::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException(ReflectionException::class);
        new Injector($object, 'failedMethod');
    }

    /**
     * @return void
     * @throws InjectorException
     * @throws ReflectionException
     */
    public function testThrowNoMatchingValueFound(): void
    {
        $object = $this->getMockBuilder(ControllerAssets::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(ProviderInterface::class)
            ->onlyMethods(['hasType', 'get'])
            ->getMock();

        $provider->expects($this->once())->method('hasType')->willReturn(true);
        $provider->expects($this->once())->method('get')->willReturn(null);

        $injector = new Injector($object, 'assetsIntAction');
        $injector->addProvider($provider);

        $this->expectException(InjectorException::class);
        $this->expectExceptionMessage('->assetsIntAction(int $key)');
        $injector->inject();
    }

    /**
     * @return void
     * @throws InjectorException
     * @throws ReflectionException
     */
    public function testThrowMultipleTypes(): void
    {
        $object = $this->getMockBuilder(ControllerAssets::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider = $this->getMockBuilder(ProviderInterface::class)
            ->onlyMethods(['hasType', 'get'])
            ->getMock();

        $injector = new Injector($object, 'assetsStubMultipleTypes');
        $injector->addProvider($provider);

        $this->expectException(InjectorException::class);
        $this->expectExceptionMessage('Injector does not support multiple types');
        $injector->inject();
    }
}