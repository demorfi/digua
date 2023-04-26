<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\File;
use Digua\Exceptions\File as FileException;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        file_put_contents(__DIR__ . '/file-test.txt', 'file test content');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unlink(__DIR__ . '/file-test.txt');
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testReadFile(): void
    {
        $file = new File(__DIR__ . '/file-test.txt');
        $this->assertTrue($file->readLock());

        $this->assertSame('file test content', $file->readLeft());
        $this->assertSame('fi', $file->readLeft(0, 2));
        $this->assertSame('le te', $file->readLeft(2, 5));
        $this->assertSame('le test content', $file->readLeft(2));

        $this->assertSame('file test content', $file->readRight());
        $this->assertSame('content', $file->readRight(7));
        $this->assertSame('n', $file->readRight(2, 1));
        $this->assertSame('content', $file->readRight(0, 7));
        $this->assertTrue($file->unlock());
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testWriteFile(): void
    {
        $file = new File(__DIR__ . '/file-test.txt');
        $this->assertTrue($file->writeLock());

        $this->assertTrue($file->writeLeft('1', 0, 1));
        $this->assertSame('1ile test content', $file->readLeft());
        $this->assertTrue($file->writeLeft('2', 2, 1));
        $this->assertSame('1i2e test content', $file->readLeft());

        $this->assertTrue($file->writeRight('1'));
        $this->assertSame('1i2e test content1', $file->readRight());
        $this->assertTrue($file->writeRight('2', 2, 1));
        $this->assertSame('1i2e test conten21', $file->readRight());

        $this->assertTrue($file->truncate(2));
        $this->assertSame('1i', $file->readLeft());

        $this->assertTrue($file->empty());
        $this->assertSame('', $file->readLeft());

        $this->assertTrue($file->unlock());
    }
}