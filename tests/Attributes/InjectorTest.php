<?php declare(strict_types=1);

namespace Tests\Attributes;

use Digua\Attributes\Injector;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Attribute;

class InjectorTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttribute(): void
    {
        $reflection = new ReflectionClass(new Injector([]));
        $attributes = $reflection->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('Attribute', $attributes[0]->getName());
        $this->assertSame([Attribute::TARGET_METHOD], $attributes[0]->getArguments());
    }

    /**
     * @return void
     */
    public function testGetInject(): void
    {
        $injector = new Injector(['key' => 'value', 'foo' => 1]);
        $this->assertSame('value', $injector->get('key'));
        $this->assertSame('1', $injector->get('foo'));
    }

    /**
     * @return void
     */
    public function testGetInjectNotFoundKey(): void
    {
        $injector = new Injector(['key' => 'value', 'foo' => 'bar']);
        $this->assertNull($injector->get('foobar'));
    }
}