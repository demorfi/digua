<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Components\DataFile as DiskFileJsonStorage;
use Digua\Enums\SortType;
use Digua\Exceptions\Storage as StorageException;
use Digua\Traits\Singleton;
use Generator;

/**
 * @method static bool staticPush(string ...$message);
 * @method static bool staticFlush(int $offset = 0);
 * @method static Generator staticGetJournal(int $limit = 0, SortType $sort = SortType::DESC);
 * @method static int staticSize();
 */
class Journal
{
    use Singleton;

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
    }

    /**
     * Add message to journal.
     *
     * @param string ...$message
     * @return bool
     * @throws StorageException
     */
    public function push(string ...$message): bool
    {
        $time    = microtime(true);
        $message = sizeof($message) < 2 ? array_shift($message) : $message;
        $dataSet = compact('message', 'time');
        return $this->dataFile->rewrite(function ($fileData) use ($dataSet) {
            $id = 'L' . (sizeof($fileData) + 1);
            $this->dataFile->overwrite($fileData);
            $this->dataFile->set($id, $dataSet);
            return array_merge($fileData, [$id => $dataSet]);
        });
    }

    /**
     * Flush journal.
     *
     * @param int $offset
     * @return bool
     * @throws StorageException
     */
    public function flush(int $offset = 0): bool
    {
        $offset > 0
            ? $this->dataFile->overwrite(array_reverse($this->getAll($offset)))
            : $this->dataFile->flush();
        return $this->dataFile->save();
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

        // A file lock shifts ordinal numbering.
        usort($journal, static fn(array $a, array $b): int => ($a['time'] == $b['time'] ? 0 : ($a['time'] < $b['time'] ? -1 : 1)));

        if ($sort == SortType::DESC) {
            $journal = array_reverse($journal);
        }

        return $limit
            ? array_slice($journal, 0, $limit)
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
            $item['date'] = date('d-m-Y H:i:s', (int)sprintf('%d', $item['time']));
            yield $key => $item;
        }
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return $this->dataFile->size();
    }
}
