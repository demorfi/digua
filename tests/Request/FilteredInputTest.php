<?php declare(strict_types=1);

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

        $mock->method('filterInput')->willReturnCallback(fn(int $type) => ['type' => $type]);
        foreach ($this->sanitize as $type) {
            $this->assertSame(['type' => $type], $mock->filteredList($type));
        }
    }

    /**
     * @return void
     */
    public function testIsItPossibleToFilteringVar(): void
    {
        $mock = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filterInput'])
            ->getMock();

        $mock->method('filterInput')->willReturnCallback(fn(int $type) => ['type' => $type]);
        foreach ($this->sanitize as $type) {
            $this->assertSame($type, $mock->filteredVar($type, 'type'));
        }
    }
}