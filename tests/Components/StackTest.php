<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\{Stack, Storage, Storage\DiskFile};
use Digua\Exceptions\{
    Storage as StorageException,
    BadMethodCall as BadMethodCallException
};
use Digua\Helper;
use PHPUnit\Framework\TestCase;
use Exception;

class StackTest extends TestCase
{
    /**
     * @var Stack[]
     */
    private array $stack = [];

    /**
     * @return void
     * @throws StorageException
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        DiskFile::setDiskPath(__DIR__);

        $this->stack['memory'] = new Stack(Storage::makeSharedMemory((string)Helper::makeIntHash(), 1048576));
        $this->stack['file']   = new Stack(Storage::makeDiskFile((string)Helper::makeIntHash()));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        foreach ($this->stack as $stack) {
            $stack->free();
        }
    }

    /**
     * @return array[]
     */
    protected function dataSetProvider(): array
    {
        return [
            'SharedMemory count 10 length 5'   => ['memory', 10, 5],
            'SharedMemory count 50 length 10'  => ['memory', 50, 10],
            'SharedMemory count 100 length 25' => ['memory', 100, 25],
            'DiskFile count 10 length 5'       => ['file', 10, 5],
            'DiskFile count 50 length 10'      => ['file', 50, 10],
            'DiskFile count 100 length 25'     => ['file', 100, 25]
        ];
    }

    /**
     * @param string $name
     * @return ?Stack
     */
    protected function getStack(string $name): ?Stack
    {
        return $this->stack[$name] ?? null;
    }

    /**
     * @dataProvider dataSetProvider
     * @param string $stack
     * @param int    $count
     * @return void
     * @throws Exception
     */
    public function testPushToMemoryStack(string $stack, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->getStack($stack)->push(range($i, $count));
        }
        $this->assertSame($count, $this->getStack($stack)->size());
    }

    /**
     * @dataProvider dataSetProvider
     * @param string $stack
     * @param int    $count
     * @return void
     * @throws Exception
     */
    public function testShadowMemoryStack(string $stack, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->getStack($stack)->push(range($i, $count));
        }

        $this->assertSame($count, $this->getStack($stack)->size());
        foreach ($this->getStack($stack)->shadow() as $value) {
            $this->assertNotEmpty($value);
        }

        $this->assertSame($count, $this->getStack($stack)->size());
    }

    /**
     * @dataProvider dataSetProvider
     * @param string $stack
     * @param int    $length
     * @param int    $count
     * @return void
     * @throws Exception
     */
    public function testPushAndReadFromMemoryStack(string $stack, int $length, int $count): void
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $this->getStack($stack)->push($range);
        }

        $this->assertSame($count, $this->getStack($stack)->size());

        $actualData = [];
        foreach ($this->getStack($stack)->read() as $item) {
            $actualData[] = $item;
        }

        $this->assertSame(0, $this->getStack($stack)->size());
        $this->assertSame($data, array_reverse($actualData));
    }

    /**
     * @dataProvider dataSetProvider
     * @param string $stack
     * @param int    $length
     * @param int    $count
     * @return void
     * @throws Exception
     */
    public function testPushAndReadReverseFromMemoryStack(string $stack, int $length, int $count): void
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $range = range(0, $length);
            shuffle($range);
            $data[] = $range;
            $this->getStack($stack)->push($range);
        }

        $this->assertSame($count, $this->getStack($stack)->size());

        $actualData = [];
        foreach ($this->getStack($stack)->readReverse() as $item) {
            $actualData[] = $item;
        }

        $this->assertSame(0, $this->getStack($stack)->size());
        $this->assertSame($data, $actualData);
    }

    /**
     * @dataProvider dataSetProvider
     * @param string $stack
     * @return void
     */
    public function testThrowProxyingCallToInstance(string $stack): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('method never does not exist!');
        $this->getStack($stack)->never();
    }
}