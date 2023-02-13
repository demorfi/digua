<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Digua\Traits\Stack as StackTrait;

class Stack
{
    use StackTrait;

    protected int $size = 1048576;
}

class StackTest extends TestCase
{
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
        $stack = new Stack();
        for ($i = 0; $i < $count; $i++) {
            $stack->push(range($i, $count));
        }
        $this->assertEquals($count, $stack->size());

        $stack = new Stack($stack->getHash());
        $this->assertEquals($count, $stack->size());
        $stack->free();
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
        $stack = new Stack();
        $data  = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $stack->push($range);
        }

        $this->assertEquals($count, $stack->size());

        $actualData = [];
        foreach ($stack->read() as $item) {
            $actualData[] = $item;
        }

        $this->assertEquals(0, $stack->size());
        $this->assertEquals($data, array_reverse($actualData));
        $stack->free();
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
        $stack = new Stack();
        $data  = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $stack->push($range);
        }

        $this->assertEquals($count, $stack->size());

        $actualData = [];
        foreach ($stack->readReverse() as $item) {
            $actualData[] = $item;
        }

        $this->assertEquals(0, $stack->size());
        $this->assertEquals($data, $actualData);
        $stack->free();
    }
}