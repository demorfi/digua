<?php declare(strict_types=1);

namespace Tests\Components\Storage;

use Digua\Components\Storage\DiskFile;
use Digua\Helper;
use PHPUnit\Framework\TestCase;
use Exception;

class DiskFileTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        DiskFile::setDiskPath(__DIR__);
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
    public function testReadFile(int $length): void
    {
        $data   = bin2hex(random_bytes($length / 2));
        $storage = DiskFile::create((string)Helper::makeIntHash());
        $this->assertTrue($storage->write($data));
        $this->assertSame($storage->getPath(), $storage->getDiskPath($storage->getName()));

        $storage = new DiskFile($storage->getName());
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
    public function testWriteFile(int $length): void
    {
        $append = '!test string!';
        $data   = bin2hex(random_bytes(($length / 2) - strlen($append)));
        $storage = DiskFile::create((string)Helper::makeIntHash());

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
    public function testRewriteFile(int $length): void
    {
        $data   = bin2hex(random_bytes($length / 2));
        $storage = DiskFile::create((string)Helper::makeIntHash());

        $this->assertTrue($storage->write('!test string!'));
        $this->assertSame('!test string!', $storage->read());

        $this->assertTrue($storage->rewrite($data));
        $this->assertSame($data, $storage->read());

        $this->assertTrue($storage->rewrite(static fn($data) => $data));
        $this->assertSame($data, $storage->read());
        $this->assertTrue($storage->free());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testEof(): void
    {
        $storage = DiskFile::create((string)Helper::makeIntHash());
        $this->assertTrue($storage->setEof());
        $this->assertTrue($storage->hasEof());
        $this->assertTrue($storage->free());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHas(): void
    {
        $name = (string)Helper::makeIntHash();
        $this->assertFalse(DiskFile::has($name));

        $storage = DiskFile::create($name);
        $this->assertTrue(DiskFile::has($name));
        $this->assertTrue($storage->free());
        $this->assertFalse(DiskFile::has($name));
    }
}