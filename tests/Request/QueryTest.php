<?php declare(strict_types=1);

namespace Request;

use PHPUnit\Framework\TestCase;
use Digua\Request\{Query, FilteredInput};
use Digua\Interfaces\Request\FilteredCollection as FilteredCollectionInterface;

class QueryTest extends TestCase
{
    /**
     * @var Query
     */
    private Query $query;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredList'])
            ->getMock();

        $input->method('filteredList')->willReturnCallback(fn() => $this->data);
        $this->query = new Query($input);
    }

    /**
     * @return void
     */
    public function testInstanceOfFilteredCollection(): void
    {
        $this->assertInstanceOf(FilteredCollectionInterface::class, $this->query);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetDataContent(): void
    {
        $this->data = ['var' => 'value', 'var2' => 'value2'];
        $this->query->shake();

        $this->assertEquals($this->data, $this->query->getAll());
    }

    /**
     * @return void
     */
    public function testWhetherTheBuildFromUriIsCalledOnShake(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([new FilteredInput])
            ->onlyMethods(['collectQueryFromUri', 'buildPathFromUri'])
            ->getMock();

        $query->expects($this->once())->method('collectQueryFromUri');
        $query->expects($this->once())->method('buildPathFromUri');
        $query->shake();
    }

    /**
     * @return void
     */
    public function testIsItCorrectBuildFromUri(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/page/1?key=value&key2=value2'];
        $this->query->shake();

        $this->assertEquals(['page'], (fn() => $this->defExport)->bindTo($this->query, Query::class)());
        $this->assertSame('value', $this->query->get('key'));
        $this->assertSame('value2', $this->query->get('key2'));
        $this->assertSame('1', $this->query->get('page'));
        $this->assertSame('/controller/action', $this->query->getPath());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetUri(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action'];
        $this->query->shake();

        $this->assertSame('/controller/action', $this->query->getUri());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetPath(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action'];
        $this->query->shake();

        $this->assertSame('/controller/action', $this->query->getPath());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetHost(): void
    {
        $this->data = ['REQUEST_SCHEME' => 'https', 'HTTP_HOST' => 'test.dot'];
        $this->query->shake();

        $this->assertSame('https://test.dot', $this->query->getHost());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetLocation(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action', 'REQUEST_SCHEME' => 'https', 'HTTP_HOST' => 'test.dot'];
        $this->query->shake();

        $this->assertSame('https://test.dot/controller/action', $this->query->getLocation());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetAsync(): void
    {
        $this->data = ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'];
        $this->query->shake();

        $this->assertTrue($this->query->isAsync());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathStringVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data'];
        $this->query->shake();

        $this->assertEmpty($this->query->getFromPath('test'));
        $this->assertEquals(['controller' => 'action'], $this->query->getFromPath('controller'));
        $this->assertEquals(['sub' => 'data'], $this->query->getFromPath('sub'));
        $this->assertEquals(
            ['controller' => 'action', 'sub' => 'data'],
            $this->query->getFromPath('controller', 'sub')
        );
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathIntVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data'];
        $this->query->shake();

        $this->assertEmpty($this->query->getFromPath(5, 0));
        $this->assertEquals(['action'], $this->query->getFromPath(2));
        $this->assertEquals(['controller', 'action'], $this->query->getFromPath(1, 2));
        $this->assertEquals(['data', 'action', 'sub'], $this->query->getFromPath(4, 2, 3, 5));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathStringAndIntVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data'];
        $this->query->shake();

        $this->assertEquals(['action', 'sub' => 'data'], $this->query->getFromPath(2, 'sub'));
        $this->assertEquals(['controller' => 'action', 'action'], $this->query->getFromPath('controller', 2));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToExportFromPath(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/dir/long/path'];
        $this->query->shake();

        $this->assertSame('/controller/action/sub/dir/long/path', $this->query->getPath());
        $this->assertNull($this->query->get('sub'));

        $result = $this->query->exportFromPath('sub');
        $this->assertInstanceOf(Query::class, $result);

        $this->assertSame('/controller/action/long/path', $this->query->getPath());
        $this->assertSame('dir', $this->query->get('sub'));

        $this->data = ['REQUEST_URI' => '/controller/action/sub/dir/long/path'];
        $this->query->shake();

        $this->query->exportFromPath('sub', 'long');
        $this->assertSame('/controller/action', $this->query->getPath());
        $this->assertSame('dir', $this->query->get('sub'));
        $this->assertSame('path', $this->query->get('long'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToBuildPath(): void
    {
        $this->query->buildPath('controller/', 'action', '/sub/');
        $this->assertSame('/controller/action/sub', $this->query->getPath());

        $this->query->buildPath('controller', 'action', 'sub');
        $this->assertSame('/controller/action/sub', $this->query->getPath());

        $query = $this->query->buildPath('/controller/action/sub/');
        $this->assertSame('/controller/action/sub', $this->query->getPath());
        $this->assertInstanceOf(Query::class, $query);
    }
}