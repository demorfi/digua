<?php

namespace Digua\Components\Journal;

use Digua\Storage as _Storage;
use Digua\Traits\Singleton;

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
     * @throws \Exception
     */
    protected function __init()
    {
        $this->journal = _Storage::load('journal');
    }

    /**
     * Add message to journal.
     *
     * @param string $message
     */
    public function push(string $message): void
    {
        $time = time();
        $this->journal->__set($this->journal->size() + 1, compact('message', 'time'));
    }

    /**
     * Flush journal.
     */
    public function flush(): void
    {
        $this->journal->flush();
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
            $journal = array_reverse($journal);
        }

        return ($limit ? array_slice($journal, 0, $limit) : $journal);
    }

    /**
     * Get journal.
     *
     * @param int $limit
     * @param int $sort
     * @return \Generator
     */
    public function getJournal(int $limit = 0, int $sort = self::SORT_DESC): \Generator
    {
        $journal = $this->getAll($limit, $sort);
        foreach ($journal as $item) {
            $item['date'] = date('Y-m-d H:m:s', $item['time']);
            yield $item;
        }
    }
}
