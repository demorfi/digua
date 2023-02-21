<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Components\DataFile as DiskFileJsonStorage;
use Digua\Enums\SortType;
use Digua\Exceptions\Storage as StorageException;
use Digua\Traits\Singleton;
use Generator;

/**
 * @method static void staticPush(string ...$message);
 * @method static void staticFlush(int $offset = 0);
 * @method static Generator staticGetJournal(int $limit = 0, SortType $sort = SortType::DESC);
 */
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
     * @var Storage|DiskFileJsonStorage
     */
    protected readonly Storage|DiskFileJsonStorage $dataFile;

    /**
     * @throws StorageException
     */
    protected function __construct()
    {
        $this->dataFile = new DataFile('journal');
        $this->dataFile->read();
        $this->original = $this->dataFile->getAll();
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
        $this->dataFile->set(strval($this->dataFile->size() + 1), compact('message', 'time'));
    }

    /**
     * Flush journal.
     */
    public function flush(int $offset = 0): void
    {
        $offset > 0
            ? $this->dataFile->overwrite(array_reverse($this->getAll($offset)))
            : $this->dataFile->flush();
    }

    /**
     * Get all journal.
     *
     * @param int      $limit
     * @param SortType $sort
     * @return array
     */
    protected function getAll(int $limit = 0, SortType $sort = SortType::DESC): array
    {
        $journal = $this->dataFile->getAll();

        if ($sort == SortType::DESC) {
            $journal = array_reverse($journal, true);
        }

        return $limit
            ? array_slice($journal, 0, $limit, true)
            : $journal;
    }

    /**
     * @param int      $limit
     * @param SortType $sort
     * @return Generator
     */
    public function getJournal(int $limit = 0, SortType $sort = SortType::DESC): Generator
    {
        $journal = $this->getAll($limit, $sort);
        foreach ($journal as $key => $item) {
            $item['date'] = date('Y-m-d H:m:s', $item['time'] ?? 0);
            yield $key => $item;
        }
    }

    /**
     * Auto save journal.
     *
     * @throws StorageException
     */
    public function __destruct()
    {
        if ($this->dataFile->size() != sizeof($this->original)
            || sizeof(@array_diff_assoc($this->dataFile->getAll(), $this->original))
        ) {
            $this->dataFile->save();
        }
    }
}
