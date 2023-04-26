<?php declare(strict_types=1);

namespace Tests\Attributes\Guardian;

use Digua\Request;
use Digua\Request\{FilteredInput, Query, Data};
use Digua\Routes\{RouteAsName, RouteAsNameBuilder};
use Digua\Attributes\Guardian;
use Digua\Attributes\Guardian\RequestPathAllowed;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Attribute;

class RequestPathAllowedTest extends TestCase
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
     * @return void
     */
    public function testInstanceOfGuardian(): void
    {
        $this->assertInstanceOf(Guardian::class, new RequestPathAllowed);
    }

    /**
     * @return void
     */
    public function testAttribute(): void
    {
        $reflection = new ReflectionClass(new RequestPathAllowed);
        $attributes = $reflection->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('Attribute', $attributes[0]->getName());
        $this->assertSame([Attribute::TARGET_METHOD], $attributes[0]->getArguments());
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
    public function testAllowedEmptyPath(): void
    {
        $attribute = new RequestPathAllowed;
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedBasePath(): void
    {
        $this->path = '/controller/action';
        $attribute  = new RequestPathAllowed;
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testNotAllowedPath(): void
    {
        $this->path = '/controller/action/foo';
        $attribute  = new RequestPathAllowed('bar');
        $this->assertFalse($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedSingleAttributePath(): void
    {
        $this->path = '/controller/action/bar';
        $attribute  = new RequestPathAllowed('bar');
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedManyAttributePath(): void
    {
        $this->path = '/controller/action/foo/value/bar/value';
        $attribute  = new RequestPathAllowed('foo', 'bar');
        $this->assertTrue($attribute->granted($this->getRoute()));
    }

    /**
     * @return void
     */
    public function testAllowedOnlyAttributePath(): void
    {
        $this->path = '/controller/action/foo/value';
        $attribute  = new RequestPathAllowed('foo', 'bar');
        $this->assertTrue($attribute->granted($this->getRoute()));

        $this->path = '/controller/action/bar/value';
        $attribute  = new RequestPathAllowed('foo', 'bar');
        $this->assertTrue($attribute->granted($this->getRoute()));
    }
}