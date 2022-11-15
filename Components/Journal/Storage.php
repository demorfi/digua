<?php

namespace Digua\Components\Journal;

use Digua\Storage as _Storage;
use Digua\Traits\Singleton;
use {Exception, Generator};

class Storage
{
    use Singleton;

    /**
     * Sort ASC.
     *
     * @var int
     */
    const SORT_ASC = 1;

    /**
     * Sort DESC.
     *
     * @var int
     */
    const SORT_DESC = 2;

    /**
     * Storage instance.
     *
     * @var _Storage
     */
    protected _Storage $journal;

    /**
     * Initialize.
     *
     * @throws Exception
     */
    protected function __init(): void
    {
        $this->journal = _Storage::load('journal');
    }

    /**
     * Add message to journal.
     *
     * @param string ...$message
     */
    public function push(string ...$message): void
    {
        $time    = time();
        $message = sizeof($message) < 2 ? array_shift($message) : $message;
        $this->journal->__set($this->journal->size() + 1, compact('message', 'time'));
    }

    /**
     * Flush journal.
     */
    public function flush(int $offset = 0): void
    {
        $offset > 0
            ? $this->journal->overwrite(array_reverse($this->getAll($offset, self::SORT_DESC)))
            : $this->journal->flush();
    }

    /**
     * Get journal.
     *
     * @param int $limit
     * @param int $sort
     * @return array
     */
    protected function getAll(int $limit = 0, int $sort = self::SORT_DESC): array
    {
        $journal = $this->journal->getAll();

        if ($sort == self::SORT_DESC) {
            $journal = array_reverse($journal, true);
        }

        return ($limit ? array_slice($journal, 0, $limit, true) : $journal);
    }

    /**
     * Get journal.
     *
     * @param int $limit
     * @param int $sort
     * @return Generator
     */
    public function getJournal(int $limit = 0, int $sort = self::SORT_DESC): Generator
    {
        $journal = $this->getAll($limit, $sort);
        foreach ($journal as $key => $item) {
            $item['date'] = date('Y-m-d H:m:s', $item['time']);
            yield $key => $item;
        }
    }
}
