<?php declare(strict_types=1);

namespace Digua\Request;

/**
 * Redefined function for testing filterInput method.
 *
 * @param string $path
 * @return string
 */
function file_get_contents(string $path): string
{
    return '{"key":"test","value":"test"}';
}

namespace Tests\Request;

use Digua\Request\FilteredInput;
use PHPUnit\Framework\TestCase;

/**
 * @backupStaticAttributes enabled
 * @runTestsInSeparateProcesses
 */
class FilteredInputTest extends TestCase
{
    /**
     * @var array
     */
    private array $sanitize;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->sanitize = (function () {
            return array_keys(static::$sanitize);
        })->bindTo(null, FilteredInput::class)();
    }

    /**
     * @return void
     */
    public function testIsItPossibleToChangeAndGetSanitizeOptions(): void
    {
        foreach ($this->sanitize as $type) {
            $options = mt_rand(PHP_INT_MIN, PHP_INT_MAX);
            $this->assertNotSame($options, FilteredInput::getSanitize($type));
            FilteredInput::setSanitize($type, $options);
            $this->assertSame($options, FilteredInput::getSanitize($type));
        }
    }

    /**
     * @return void
     */
    public function testRefreshInput(): void
    {
        $mock = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filterInput'])
            ->getMock();

        $mock->expects($this->exactly(2))->method('filterInput');

        $mock->filteredList(INPUT_POST);
        $mock->filteredList(INPUT_POST);
        $result = $mock->refresh(INPUT_POST);
        $this->assertInstanceOf(FilteredInput::class, $result);
        $mock->filteredList(INPUT_POST);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringList(): void
    {
        $mock = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filterInput'])
            ->getMock();

        $mock->method('filterInput')->willReturnCallback(static fn(int $type) => ['type' => $type]);
        foreach ($this->sanitize as $type) {
            $this->assertSame(['type' => $type], $mock->filteredList($type));
        }
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringListServerType(): void
    {
        $this->assertSame(filter_var_array($_SERVER), (new FilteredInput)->filteredList(FilteredInput::INPUT_SERVER));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringListPostType(): void
    {
        $input = new FilteredInput;

        $_SERVER['CONTENT_TYPE'] = 'application/text';
        $this->assertEmpty($input->filteredList(FilteredInput::INPUT_POST));

        // function file_get_contents redefined!
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $input->refresh(FilteredInput::INPUT_POST);
        $this->assertSame(['key' => 'test', 'value' => 'test'], $input->filteredList(FilteredInput::INPUT_POST));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringListFilesType(): void
    {
        $_FILES = [
            'file1' => ['name' => ['test.file', 'test2.file'], 'tmp_name' => ['/tmp/1', '/tmp/2']],
            'file2' => ['name' => 'test3.file', 'tmp_name' => '/tmp/3']
        ];

        $input  = new FilteredInput;
        $expect = [
            ['field' => 'file1', 'count' => 2, 'index' => 0, 'name' => 'test.file', 'tmp_name' => '/tmp/1'],
            ['field' => 'file1', 'count' => 2, 'index' => 1, 'name' => 'test2.file', 'tmp_name' => '/tmp/2'],
            ['field' => 'file2', 'count' => 1, 'index' => 0, 'name' => 'test3.file', 'tmp_name' => '/tmp/3'],
        ];

        $this->assertSame($expect, $input->filteredList(FilteredInput::INPUT_FILES));
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringVar(): void
    {
        $mock = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filterInput'])
            ->getMock();

        $mock->method('filterInput')->willReturnCallback(static fn(int $type) => ['type' => $type]);
        foreach ($this->sanitize as $type) {
            $this->assertSame($type, $mock->filteredVar($type, 'type'));
        }
    }
}