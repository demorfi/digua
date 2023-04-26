<?php declare(strict_types=1);

namespace Tests\Attributes\Guardian;

use Digua\Request;
use Digua\Request\{FilteredInput, Query, Data};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Attributes\Guardian;
use Digua\Attributes\Guardian\RequestPathRequired;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Attribute;

class RequestPathRequiredTest extends TestCase
{
    /**
     * @var string
     */
    private string $path = '/';

    /**
     * @var FilteredInput
     */
    private FilteredInput $input;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredVar'])
            ->getMock();

        $this->input->method('filteredVar')->willReturnCallback(fn() => $this->path);
    }

    /**
     * @return RouteAsName
     */
    protected function getRoute(): RouteAsName
    {
        $query   = new Query($this->input);
        $builder = new RouteAsNameBuilder(new Request(new Data(query: $query)));
        return new RouteAsName('\App\Controllers\\', $builder);
    }

    /**
     * @return void
     */
    public function testInstanceOfGuardian(): void
    {
        $this->assertInstanceOf(Guardian::class, new RequestPathRequired);
    }

    /**
     * @return void
     */
    public function testAttribute(): void
    {
        $reflection = new ReflectionClass(new RequestPathRequired);
        $attributes = $reflection->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('Attribute', $attributes[0]->getName());
        $this->assertSame([Attribute::TARGET_METHOD], $attributes[0]->getArguments());
    }

    /**
     * @return void
     */
    public function testNotRequiredPath(): void
    {
        $attribute = new RequestPathRequired;
        $this->assertTrue($attribute->granted($this->getRoute()));

        $this->path = '/controller/action/foo';
        $attribute  = new RequestPathRequired;
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testFailedRequireSingleAttributePath(): void
    {
        $this->path = '/controller/action/foo';
        $attribute  = new RequestPathRequired('bar');
        $this->assertFalse($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedRequireSingleAttributePath(): void
    {
        $this->path = '/controller/action/bar';
        $attribute  = new RequestPathRequired('bar');
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testFailedRequireManyAttributePath(): void
    {
        $this->path = '/controller/action/foo/value';
        $attribute  = new RequestPathRequired('foo', 'bar');
        $this->assertFalse($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedRequireManyAttributePath(): void
    {
        $this->path = '/controller/action/foo/value/bar/value';
        $attribute  = new RequestPathRequired('foo', 'bar');
        $this->assertTrue($attribute->granted($this->getRoute()));
    }
}