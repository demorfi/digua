<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\{Storage, Storage\DiskFile};
use Digua\Helper;
use Digua\Interfaces\Storage as StorageInterface;
use Digua\Exceptions\{
    Storage as StorageException,
    BadMethodCall as BadMethodCallException
};
use Tests\Pacifiers\StubStorage;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
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
     * @return void
     */
    public function testIsItPossibleCreateStorageInstance(): void
    {
        $storage = new Storage(StubStorage::class, 'first-argument', 'last-argument');
        $this->assertInstanceOf(StorageInterface::class, $storage->getInstance());
        $this->assertSame($storage->getInstance()->arguments, ['first-argument', 'last-argument']);
    }

    /**
     * @return void
     */
    public function testThrowInvalidStorageInstance(): void
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Storage (' . self::class . ') not found!');
        new Storage(self::class);
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testIsItPossibleStaticMakeStorageInstance(): void
    {
        $storage = Storage::make(StubStorage::class, 'first-argument', 'last-argument');
        $this->assertInstanceOf(StorageInterface::class, $storage->getInstance());
        $this->assertSame($storage->getInstance()->arguments, ['first-argument', 'last-argument']);
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testIsItPossibleMakeDefinedStorageInstance(): void
    {
        $storage = Storage::makeSharedMemory((string)Helper::makeIntHash(), 1048576);
        $this->assertInstanceOf(StorageInterface::class, $storage->getInstance());
        $storage->free();

        $storage = Storage::makeDiskFile((string)Helper::makeIntHash());
        $this->assertInstanceOf(StorageInterface::class, $storage->getInstance());
        $storage->free();
    }

    /**
     * @return void
     */
    public function testIsItPossibleProxyingCallToStorageInstance(): void
    {
        $storage = new Storage(StubStorage::class);
        $this->assertSame($storage->read(), 'Tests\Pacifiers\StubStorage::read');
    }

    /**
     * @return void
     */
    public function testThrowProxyingCallToStorageInstance(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method (never) does not exist!');

        $storage = new Storage(StubStorage::class);
        $storage->never();
    }
}