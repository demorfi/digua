<?php declare(strict_types=1);

namespace Tests\Request;

use Digua\Request\{Query, FilteredInput};
use Digua\Interfaces\Request\FilteredCollection as FilteredCollectionInterface;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * @var FilteredInput
     */
    private FilteredInput $input;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredList'])
            ->getMock();

        $this->input->method('filteredList')->willReturnCallback(fn() => $this->data);
    }

    /**
     * @return void
     */
    public function testInstanceOfFilteredCollection(): void
    {
        $query = new Query($this->input);
        $this->assertInstanceOf(FilteredCollectionInterface::class, $query);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetDataContent(): void
    {
        $this->data = ['var' => 'value', 'var2' => 'value2'];

        $query = new Query($this->input);
        $this->assertSame($this->data, $query->getAll());
    }

    /**
     * @return void
     */
    public function testWhetherCollectFromUriIsCalledOnShake(): void
    {
        $query = $this->getMockBuilder(Query::class)
            ->setConstructorArgs([new FilteredInput])
            ->onlyMethods(['collectPathFromUri', 'collectQueryFromUri'])
            ->getMock();

        $query->expects($this->once())->method('collectPathFromUri');
        $query->expects($this->once())->method('collectQueryFromUri');
        $query->shake();
    }

    /**
     * @return void
     */
    public function testIsItCorrectBuildFromUri(): void
    {
        $this->data = ['REQUEST_URI' => '/u-controller/u-action/u-page/1?key=value&key2=value2'];

        $query = new Query($this->input);
        $this->assertSame('value', $query->get('key'));
        $this->assertSame('value2', $query->get('key2'));
        $this->assertSame('1', $query->get('uPage'));
        $this->assertSame('u-action', $query->get('uController'));
        $this->assertSame('/u-controller/u-action/u-page/1', $query->getPath());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetUri(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action'];

        $query = new Query($this->input);
        $this->assertSame('/controller/action', $query->getUri());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetPath(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action'];

        $query = new Query($this->input);
        $this->assertSame('/controller/action', $query->getPath());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetPathAsList(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/page/1/sub/dir'];

        $query = new Query($this->input);
        $this->assertSame(['controller' => 'action', 'page' => '1', 'sub' => 'dir'], $query->getPathAsList());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetHost(): void
    {
        $this->data = ['REQUEST_SCHEME' => 'https', 'HTTP_HOST' => 'test.dot'];

        $query = new Query($this->input);
        $this->assertSame('https://test.dot', $query->getHost());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetLocation(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action', 'REQUEST_SCHEME' => 'https', 'HTTP_HOST' => 'test.dot'];

        $query = new Query($this->input);
        $this->assertSame('https://test.dot/controller/action', $query->getLocation());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetAsync(): void
    {
        $this->data = ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'];

        $query = new Query($this->input);
        $this->assertTrue($query->isAsync());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathStringVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data'];

        $query = new Query($this->input);
        $this->assertEmpty($query->getFromPath('test'));
        $this->assertSame(['controller' => 'action'], $query->getFromPath('controller'));
        $this->assertSame(['sub' => 'data'], $query->getFromPath('sub'));
        $this->assertSame(
            ['controller' => 'action', 'sub' => 'data'],
            $query->getFromPath('controller', 'sub')
        );
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathIntVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data/1/2'];

        $query = new Query($this->input);
        $this->assertSame(['action'], $query->getFromPath(2));
        $this->assertSame(['controller', 'action'], $query->getFromPath(1, 2));
        $this->assertSame(['data', 'action', 'sub', '1', '2'], $query->getFromPath(4, 2, 3, 5, 6));
        $this->assertSame(['1', '2'], $query->getFromPath(5, 6, 7));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetFromPathStringAndIntVariables(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/data'];

        $query = new Query($this->input);
        $this->assertSame(['action', 'sub' => 'data'], $query->getFromPath(2, 'sub'));
        $this->assertSame(['controller' => 'action', 'action'], $query->getFromPath('controller', 2));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToExportFromPath(): void
    {
        $this->data = ['REQUEST_URI' => '/controller/action/sub/dir/long/path'];

        $query = new Query($this->input);
        $this->assertSame('/controller/action/sub/dir/long/path', $query->getPath());
        $this->assertSame('dir', $query->get('sub'));

        $result = $query->exportFromPath('sub');
        $this->assertInstanceOf(Query::class, $result);

        $this->assertSame('/controller/action/long/path', $query->getPath());
        $this->assertSame('dir', $query->get('_sub_'));

        $this->data = ['REQUEST_URI' => '/controller/action/sub/dir/long/path'];

        $query = new Query($this->input);
        $query->exportFromPath('sub', 'long');

        $this->assertSame('/controller/action', $query->getPath());
        $this->assertSame('dir', $query->get('_sub_'));
        $this->assertSame('path', $query->get('_long_'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToBuildPath(): void
    {
        $query = new Query($this->input);
        $query->buildPath('controller/', 'action', '/sub/');
        $this->assertSame('/controller/action/sub', $query->getPath());

        $query->buildPath('controller', 'action', 'sub');
        $this->assertSame('/controller/action/sub', $query->getPath());

        $result = $query->buildPath('/controller/action/sub/');
        $this->assertSame('/controller/action/sub', $query->getPath());
        $this->assertInstanceOf(Query::class, $result);
    }
}