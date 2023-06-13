<?php declare(strict_types=1);

namespace Tests\Components\Storage;

use Digua\Components\Storage\SharedMemory;
use Digua\Helper;
use Digua\Exceptions\{Memory as MemoryException, MemoryShared as MemorySharedException};
use PHPUnit\Framework\TestCase;
use Exception;

class SharedMemoryTest extends TestCase
{
    /**
     * @param int $length
     * @testWith
     * [1024]
     * [1048576]
     * [10485760]
     * @return void
     * @throws Exception
     */
    public function testReadMemory(int $length): void
    {
        $data    = bin2hex(random_bytes($length / 2));
        $storage = SharedMemory::create($length);
        $this->assertTrue($storage->write($data));
        $this->assertSame($storage->getName() . ':' . $length, $storage->getPath());

        $storage = new SharedMemory($storage->getName(), $length);
        $this->assertSame($data, $storage->read());
        $this->assertTrue($storage->free());
    }

    /**
     * @param int $length
     * @testWith
     * [1024]
     * [1048576]
     * [10485760]
     * @return void
     * @throws Exception
     */
    public function testWriteMemory(int $length): void
    {
        $append  = '!test string!';
        $data    = bin2hex(random_bytes(($length / 2) - strlen($append)));
        $storage = SharedMemory::create($length);

        $this->assertTrue($storage->write($data));
        $this->assertSame($data, $storage->read());

        $this->assertTrue($storage->write('!test string!'));
        $this->assertSame($data . '!test string!', $storage->read());
        $this->assertTrue($storage->free());
    }

    /**
     * @param int $length
     * @testWith
     * [1024]
     * [1048576]
     * [10485760]
     * @return void
     * @throws Exception
     */
    public function testRewriteMemory(int $length): void
    {
        $data    = bin2hex(random_bytes($length / 2));
        $storage = SharedMemory::create($length);

        $this->assertTrue($storage->write('!test string!'));
        $this->assertSame('!test string!', $storage->read());

        $this->assertTrue($storage->rewrite($data));
        $this->assertSame($data, $storage->read());

        $this->assertTrue($storage->rewrite(fn($data) => $data));
        $this->assertSame($data, $storage->read());
        $this->assertTrue($storage->free());
    }

    /**
     * @return void
     * @throws MemoryException
     */
    public function testEof(): void
    {
        $storage = SharedMemory::create(1);
        $this->assertTrue($storage->setEof());
        $this->assertTrue($storage->hasEof());
        $this->assertTrue($storage->free());
    }

    /**
     * @return void
     * @throws MemoryException
     */
    public function testHas(): void
    {
        $name = (string)Helper::makeIntHash();
        $this->assertFalse(SharedMemory::has($name));

        $storage = new SharedMemory($name, 1);
        $this->assertTrue(SharedMemory::has($name));
        $this->assertTrue($storage->free());
        $this->assertFalse(SharedMemory::has($name));
    }

    /**
     * @return void
     * @throws MemorySharedException
     * @throws MemoryException
     */
    public function testThrowOutOfMemory(): void
    {
        $storage = SharedMemory::create(1);
        $this->expectException(MemorySharedException::class);
        $storage->write('overflow');
    }
}