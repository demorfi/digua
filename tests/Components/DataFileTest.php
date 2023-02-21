<?php declare(strict_types=1);

namespace Components;

use Digua\Components\DataFile;
use Digua\Components\Storage\DiskFile;
use Digua\Helper;
use Digua\Exceptions\Storage as StorageException;
use PHPUnit\Framework\TestCase;
use Exception;

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', null);
}

class DataFileTest extends TestCase
{
    /**
     * @var DataFile
     */
    private DataFile $dataFile;

    /**
     * @return void
     * @throws StorageException
     */
    protected function setUp(): void
    {
        DiskFile::setDiskPath(__DIR__);
        $this->dataFile = DataFile::create((string)Helper::makeIntHash());
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dataFile->free();
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
    public function testReadData(int $count): void
    {
        $data = range(1, $count);
        $this->assertEmpty($this->dataFile->read());
        $this->assertTrue($this->dataFile->write($data));
        $this->assertEquals($data, $this->dataFile->read());
        $this->assertEquals(sizeof($data), $this->dataFile->size());
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
    public function testWriteData(int $count): void
    {
        $data = range(1, $count);
        $this->assertTrue($this->dataFile->write($data));
        $this->assertEquals($data, $this->dataFile->read());

        $this->assertTrue($this->dataFile->write(['name1' => 'value1']));
        $this->dataFile->set('name2', 'value2');
        $this->dataFile->save();
        $this->assertEquals(array_merge($data, ['name1' => 'value1', 'name2' => 'value2']), $this->dataFile->read());
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
    public function testRewriteData(int $count): void
    {
        $data = range(1, $count);
        $this->assertTrue($this->dataFile->write($data));
        $this->assertEquals($data, $this->dataFile->read());

        $this->assertTrue($this->dataFile->rewrite(['test' => 'value']));
        $this->assertEquals(['test' => 'value'], $this->dataFile->read());
    }
}