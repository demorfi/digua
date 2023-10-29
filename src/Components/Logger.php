<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Enums\FileExtension;
use Digua\Interfaces\{
    Logger as LoggerInterface,
    Storage as StorageInterface
};
use Digua\Traits\Singleton;
use Digua\Exceptions\{
    Base as BaseException,
    Storage as StorageException
};

/**
 * @method static void staticPush(string $message);
 * @method static void staticSave();
 */
class Logger implements LoggerInterface
{
    use Singleton;

    /**
     * @var array
     */
    protected array $queue = [];

    /**
     * @var array
     */
    protected array $pushed = [];

    /**
     * @var Storage[]|StorageInterface[]
     */
    protected array $storages = [];

    /**
     * @param Storage|StorageInterface $storage
     * @return void
     */
    public function addStorage(Storage|StorageInterface $storage): void
    {
        $this->storages[] = $storage;
    }

    /**
     * @return array
     */
    public function getPushed(): array
    {
        return $this->pushed;
    }

    /**
     * @return array
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * @return void
     */
    public function clearQueue(): void
    {
        $this->queue = [];
    }

    /**
     * @inheritdoc
     * @testWith ('message')
     */
    public function push(string $message): void
    {
        $date          = date('d-m-Y H:i:s');
        $this->queue[] = compact('date', 'message');
    }

    /**
     * @return void
     * @throws StorageException
     */
    public function save(): void
    {
        if (empty($this->queue)) {
            return;
        }

        // default storage
        if (empty($this->storages)) {
            $this->storages[] = Storage::makeDiskFile('digua' . FileExtension::LOG->value);
        }

        try {
            $messages = array_reduce($this->queue, function ($carry, $item) {
                return $carry . '[' . $item['date'] . '] ' . $item['message'] . "\n";
            }, '');

            foreach ($this->storages as $storage) {
                $storage->write($messages);
            }
        } catch (BaseException) {
        }

        $this->pushed = [...$this->pushed, ...$this->queue];
        $this->clearQueue();
    }

    /**
     * Auto save.
     *
     * @throws StorageException
     */
    public function __destruct()
    {
        $this->save();
    }
}