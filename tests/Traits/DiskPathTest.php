<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Traits\DiskPath;
use Digua\Exceptions\Path as PathException;
use PHPUnit\Framework\TestCase;

class DiskPathTest extends TestCase
{
    /**
     * @var object
     */
    private object $traitDiskPath;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->traitDiskPath = new class {
            use DiskPath {
                appendToDiskPath as public;
            }
        };
    }

    /**
     * @return void
     */
    public function testSetDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertSame($this->traitDiskPath::getConfigValue('diskPath'), '/testPath');

        $this->traitDiskPath::setDiskPath('/testPath/subPath');
        $this->assertSame($this->traitDiskPath::getConfigValue('diskPath'), '/testPath/subPath');
    }

    /**
     * @return void
     */
    public function testGetDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertSame($this->traitDiskPath::getDiskPath(), '/testPath');
    }

    /**
     * @return void
     */
    public function testGetDiskAppendPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertSame($this->traitDiskPath::getDiskPath('/appendPath/'), '/testPath/appendPath');
    }

    /**
     * @return void
     */
    public function testAppendToDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertSame($this->traitDiskPath::appendToDiskPath('/appendPath/'), '/testPath/appendPath');
        $this->assertSame($this->traitDiskPath::getDiskPath(), '/testPath/appendPath');
    }

    /**
     * @return void
     */
    public function testIsReadableDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertFalse($this->traitDiskPath::isReadableDiskPath());

        $this->traitDiskPath::setDiskPath(__DIR__);
        $this->assertTrue($this->traitDiskPath::isReadableDiskPath());
    }

    /**
     * @return void
     */
    public function testIsWritableDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->assertFalse($this->traitDiskPath::isWritableDiskPath());

        $this->traitDiskPath::setDiskPath(__DIR__);
        $this->assertTrue($this->traitDiskPath::isWritableDiskPath());
    }

    /**
     * @return void
     */
    public function testThrowIsBrokenDiskPath(): void
    {
        $this->traitDiskPath::setDiskPath(__DIR__);
        $this->assertFalse($this->traitDiskPath::throwIsBrokenDiskPath());
    }

    /**
     * @return void
     */
    public function testThrowIsNotConfiguredDiskPath(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('The disk path for (' . $this->traitDiskPath::class . ') is not configured!');
        $this->expectExceptionCode(100);

        $this->traitDiskPath::setConfigValue('diskPath', null);
        $this->traitDiskPath::throwIsBrokenDiskPath();
    }

    /**
     * @return void
     */
    public function testThrowIsNotReadableDiskPath(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('The disk path (/testPath) is not readable!');
        $this->expectExceptionCode(200);

        $this->traitDiskPath::setDiskPath('/testPath/');
        $this->traitDiskPath::throwIsBrokenDiskPath();
    }
}