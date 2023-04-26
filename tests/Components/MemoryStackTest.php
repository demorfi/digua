<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\{Stack, Storage};
use Digua\Exceptions\Storage as StorageException;
use Digua\Helper;
use PHPUnit\Framework\TestCase;
use Exception;

class MemoryStackTest extends TestCase
{
    private Stack $stack;

    /**
     * @return void
     * @throws StorageException
     */
    protected function setUp(): void
    {
        $this->stack = new Stack(Storage::makeSharedMemory((string)Helper::makeIntHash(), 1048576));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->stack->free();
    }

    /**
     * @param int $count
     * @testWith
     * [10]
     * [50]
     * [100]
     * @return void
     * @throws Exception
     */
    public function testPushToMemoryStack(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->stack->push(range($i, $count));
        }
        $this->assertSame($count, $this->stack->size());
    }

    /**
     * @param int $count
     * @testWith
     * [10]
     * [50]
     * [100]
     * @return void
     * @throws Exception
     */
    public function testShadowMemoryStack(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->stack->push(range($i, $count));
        }

        $this->assertSame($count, $this->stack->size());
        foreach ($this->stack->shadow() as $value) {
            $this->assertNotEmpty($value);
        }

        $this->assertSame($count, $this->stack->size());
    }

    /**
     * @param int $length
     * @param int $count
     * @testWith
     * [5, 10]
     * [10, 30]
     * [25, 100]
     * @return void
     * @throws Exception
     */
    public function testPushAndReadFromMemoryStack(int $length, int $count): void
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $this->stack->push($range);
        }

        $this->assertSame($count, $this->stack->size());

        $actualData = [];
        foreach ($this->stack->read() as $item) {
            $actualData[] = $item;
        }

        $this->assertSame(0, $this->stack->size());
        $this->assertSame($data, array_reverse($actualData));
    }

    /**
     * @param int $length
     * @param int $count
     * @testWith
     * [5, 10]
     * [10, 30]
     * [25, 100]
     * @return void
     * @throws Exception
     */
    public function testPushAndReadReverseFromMemoryStack(int $length, int $count): void
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $this->stack->push($range);
        }

        $this->assertSame($count, $this->stack->size());

        $actualData = [];
        foreach ($this->stack->readReverse() as $item) {
            $actualData[] = $item;
        }

        $this->assertSame(0, $this->stack->size());
        $this->assertSame($data, $actualData);
    }
}