<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\{Logger, Storage, Storage\DiskFile};
use Digua\Interfaces\Logger as LoggerInterface;
use Digua\Exceptions\Storage as StorageException;
use PHPUnit\Framework\{TestCase, MockObject\MockObject};

class LoggerTest extends TestCase
{
    /**
     * @var Storage|MockObject
     */
    private Storage|MockObject $storage;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        DiskFile::setDiskPath(__DIR__);

        $this->logger  = Logger::getInstance();
        $this->storage = $this->getMockBuilder(Storage::class)
            ->disableOriginalConstructor()
            ->addMethods(['write'])
            ->getMock();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $filePath = __DIR__ . '/digua.log';
        is_file($filePath) && unlink($filePath);

        $this->logger->clearQueue();
    }

    /**
     * @return void
     */
    public function testInstanceOfInterface(): void
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->logger);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetQueue(): void
    {
        $this->assertEmpty($this->logger->getQueue());

        $date = date('d-m-Y H:i:s');
        $this->logger->push(__METHOD__ . '-1');
        $this->logger->push(__METHOD__ . '-2');

        $queue = $this->logger->getQueue();
        $this->assertSame($queue, [['date' => $date, 'message' => __METHOD__ . '-1'], ['date' => $date, 'message' => __METHOD__ . '-2']]);
    }

    /**
     * @runInSeparateProcess
     * @return void
     * @throws StorageException
     */
    public function testIsItPossiblePushDefaultStorage(): void
    {
        $this->logger->push(__METHOD__ . '-1');
        $this->logger->push(__METHOD__ . '-2');

        $this->logger->save();
        $dataFile = file_get_contents(__DIR__ . '/digua.log');

        $this->assertTrue(str_contains($dataFile, __METHOD__ . '-1'));
        $this->assertTrue(str_contains($dataFile, __METHOD__ . '-2'));
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testPushedMessagesIsReturned(): void
    {
        $this->storage->expects($this->once())->method('write');
        $this->logger->addStorage($this->storage);

        $date = date('d-m-Y H:i:s');
        $this->logger->push(__METHOD__ . '-1');
        $this->logger->push(__METHOD__ . '-2');
        $this->logger->save();

        $pushed = $this->logger->getPushed();
        $this->assertSame($pushed, [['date' => $date, 'message' => __METHOD__ . '-1'], ['date' => $date, 'message' => __METHOD__ . '-2']]);
    }

    /**
     * @runInSeparateProcess
     * @return void
     * @throws StorageException
     */
    public function testIsItPossiblePushToAddedStorage(): void
    {
        $message = __METHOD__;
        $this->storage->expects($this->once())->method('write')
            ->willReturnCallback(
                function (string $messages) use ($message) {
                    $this->assertTrue(str_contains($messages, $message . '-1'));
                    $this->assertTrue(str_contains($messages, $message . '-2'));
                }
            );
        $this->logger->addStorage($this->storage);

        $this->logger->push($message . '-1');
        $this->logger->push($message . '-2');
        $this->logger->save();
        $this->assertFalse(is_file(__DIR__ . '/digua.log'));
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testIsItPossibleClearQueue(): void
    {
        $this->storage->expects($this->never())->method('write');
        $this->logger->addStorage($this->storage);

        $this->logger->push(__METHOD__ . '-1');
        $this->logger->clearQueue();

        $this->assertEmpty($this->logger->getQueue());
        $this->logger->save();
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testIsItPossibleAutoSaveLog(): void
    {
        $this->storage->expects($this->once())->method('write');
        $this->logger->addStorage($this->storage);

        $this->logger->push(__METHOD__ . '-1');
        $this->logger->__destruct();
    }
}