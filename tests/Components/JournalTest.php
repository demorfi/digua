<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\Storage\DiskFile;
use Digua\Components\Journal;
use Digua\Exceptions\Storage as StorageException;
use Digua\Enums\{SortType, FileExtension};
use PHPUnit\Framework\TestCase;

class JournalTest extends TestCase
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
    protected function tearDown(): void
    {
        @unlink(__DIR__ . '/journal' . FileExtension::JDB->value);
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testPushToJournal(): void
    {
        $this->assertTrue(Journal::staticPush('test message!'));
        sleep(1);
        $this->assertTrue(Journal::staticPush('test message 2!'));
        $this->assertTrue(Journal::getInstance()->push('test message 3!'));
        $this->assertSame(3, Journal::staticSize());
    }

    /**
     * @depends testPushToJournal
     * @return void
     */
    public function testReadingJournal(): void
    {
        $messages = [];
        foreach (Journal::staticGetJournal(2) as $message) {
            $messages[] = $message['message'];
            $this->assertSame($message['date'], date('d-m-Y H:i:s', (int)sprintf('%d', $message['time'])));
        }
        $this->assertSame(['test message 3!', 'test message 2!'], $messages);

        $messages = [];
        foreach (Journal::staticGetJournal(sort: SortType::ASC) as $message) {
            $messages[] = $message['message'];
            $this->assertSame($message['date'], date('d-m-Y H:i:s', (int)sprintf('%d', $message['time'])));
        }
        $this->assertSame(['test message!', 'test message 2!', 'test message 3!'], $messages);
    }

    /**
     * @runInSeparateProcess
     * @return void
     * @throws StorageException
     */
    public function testFreeJournal(): void
    {
        $this->assertTrue(Journal::getInstance()->flush());
    }
}