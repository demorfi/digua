<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Digua\Components\Memory;

class MemoryTest extends TestCase
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
    public function testReadAndWriteMemory(int $length): void
    {
        $data   = random_bytes($length);
        $memory = Memory::create($length);

        $this->assertEmpty($memory->read());
        $this->assertTrue($memory->write($data));
        $this->assertSame($data, $memory->read());

        $this->assertTrue($memory->write('!test string!'));
        $this->assertSame('!test string!', $memory->read());

        $this->assertTrue($memory->setEof());
        $this->assertTrue($memory->hasEof());
        $this->assertTrue($memory->free());
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
    public function testRestoreMemory(int $length): void
    {
        $data   = random_bytes($length);
        $memory = Memory::create($length);
        $this->assertTrue($memory->write($data));

        $memory = Memory::restore($memory->getHash());
        $this->assertSame($data, $memory->read());
        $this->assertTrue($memory->free());
    }
}