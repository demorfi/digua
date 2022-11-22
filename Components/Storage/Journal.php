<?php

namespace Digua\Components\Storage;

use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};
use Digua\Traits\Singleton;
use Digua\Enums\SortType;
use Generator;

class Journal
{
    use Singleton;

    /**
     * Original data for diff.
     *
     * @var array
     */
    private readonly array $original;

    /**
     * Storage instance.
     *
     * @var Json
     */
    protected readonly Json $journal;

    /**
     * Initialize.
     *
     * @return void
     * @throws PathException
     * @throws StorageException
     */
    protected function __init(): void
    {
        $this->journal  = Json::load('journal');
        $this->original = $this->journal->getAll();
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
            ? $this->journal->overwrite(array_reverse($this->getAll($offset, SortType::DESC)))
            : $this->journal->flush();
    }

    /**
     * Get journal.
     *
     * @param int      $limit
     * @param SortType $sort
     * @return array
     */
    protected function getAll(int $limit = 0, SortType $sort = SortType::DESC): array
    {
        $journal = $this->journal->getAll();

        if ($sort == SortType::DESC) {
            $journal = array_reverse($journal, true);
        }

        return $limit
            ? array_slice($journal, 0, $limit, true)
            : $journal;
    }

    /**
     * Get journal.
     *
     * @param int      $limit
     * @param SortType $sort
     * @return Generator
     */
    public function getJournal(int $limit = 0, SortType $sort = SortType::DESC): Generator
    {
        $journal = $this->getAll($limit, $sort);
        foreach ($journal as $key => $item) {
            $item['date'] = date('Y-m-d H:m:s', $item['time']);
            yield $key => $item;
        }
    }

    /**
     * Auto save storage.
     *
     * @throws StorageException
     */
    public function __destruct()
    {
        if ($this->journal->size() != sizeof($this->original)
            || sizeof(@array_diff_assoc($this->journal->getAll(), $this->original))
        ) {
            $this->journal->save();
        }
    }
}
