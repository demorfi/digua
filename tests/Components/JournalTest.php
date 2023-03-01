<?php declare(strict_types=1);

namespace Components;

use Digua\Components\Storage\DiskFile;
use Digua\Components\Journal as JournalComponent;
use Digua\Exceptions\Storage as StorageException;
use Digua\Enums\SortType;
use PHPUnit\Framework\TestCase;

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', null);
}

class Journal extends JournalComponent
{
    /**
     * @return bool
     */
    public function free(): bool
    {
        $this->dataFile->flush();
        return $this->dataFile->free();
    }
}

class JournalTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        DiskFile::setDiskPath(__DIR__);
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function testPushToJournal(): void
    {
        $this->assertTrue(Journal::staticFlush());

        $this->assertTrue(Journal::staticPush('test message!'));
        $this->assertTrue(Journal::staticPush('test message 2!'));
        $this->assertTrue(Journal::getInstance()->push('test message 3!'));

        $this->assertSame(3, Journal::staticSize());
    }

    /**
     * @return void
     */
    public function testReadingJournal(): void
    {
        $messages = [];
        foreach (Journal::staticGetJournal(2) as $message) {
            $messages[] = $message['message'];
            $this->assertEquals($message['date'], date('Y-m-d H:m:s', $message['time']));
        }
        $this->assertEquals(['test message 3!', 'test message 2!'], $messages);

        $messages = [];
        foreach (Journal::staticGetJournal(sort: SortType::ASC) as $message) {
            $messages[] = $message['message'];
            $this->assertEquals($message['date'], date('Y-m-d H:m:s', $message['time']));
        }
        $this->assertEquals(['test message!', 'test message 2!', 'test message 3!'], $messages);
    }

    /**
     * @return void
     */
    public function testFreeJournal(): void
    {
        $this->assertTrue(Journal::getInstance()->free());
    }
}