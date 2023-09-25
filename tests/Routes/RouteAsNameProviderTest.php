<?php declare(strict_types=1);

namespace Tests\Routes;

use Digua\Routes\RouteAsNameProvider;
use Digua\Request;
use Digua\Request\{Data, Query, FilteredInput};
use PHPUnit\Framework\TestCase;

class RouteAsNameProviderTest extends TestCase
{
    /**
     * @var RouteAsNameProvider
     */
    private RouteAsNameProvider $provider;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredVar'])
            ->getMock();

        $input->method('filteredVar')
            ->willReturnCallback(fn() => '/a-key/1/b-key/string');

        $request        = new Request(new Data(query: new Query($input)));
        $this->provider = new RouteAsNameProvider($request);
    }

    /**
     * @return array[]
     */
    protected function typesProvider(): array
    {
        return [
            'int'    => ['int'],
            'float'  => ['float'],
            'string' => ['string'],
            'bool'   => ['bool']
        ];
    }

    /**
     * @return array
     */
    protected function dataTypePathProvider(): array
    {
        return [
            'int key value 1'         => ['aKey', 'int', 1],
            'int key value string'    => ['bKey', 'int', 0],
            'float key value 1'       => ['aKey', 'float', 1.0],
            'float key value string'  => ['bKey', 'float', 0.0],
            'string key value 1'      => ['aKey', 'string', '1'],
            'string key value string' => ['bKey', 'string', 'string'],
            'bool key value 1'        => ['aKey', 'bool', true],
            'bool key value string'   => ['bKey', 'bool', true],
        ];
    }

    /**
     * @dataProvider typesProvider
     * @param string $type
     * @return void
     */
    public function testHasType(string $type): void
    {
        $this->assertTrue($this->provider->hasType($type));
    }

    /**
     * @return void
     */
    public function testHasInvalidType(): void
    {
        $this->assertFalse($this->provider->hasType('foobar'));
    }

    /**
     * @dataProvider dataTypePathProvider
     * @param string $key
     * @param string $type
     * @param mixed  $value
     * @return void
     */
    public function testGetValueOfType(string $key, string $type, mixed $value): void
    {
        $this->assertSame($value, $this->provider->get($key, $type));
    }

    /**
     * @dataProvider typesProvider
     * @param string $type
     * @return void
     */
    public function testTryingToGetInvalidType(string $type): void
    {
        $this->assertSame(null, $this->provider->get('foo', $type));
    }
}