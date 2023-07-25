<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Enums\FileExtension;
use Digua\Interfaces\{
    Logger as LoggerInterface,
    Storage as StorageInterface
};
use Digua\Traits\Singleton;
use Digua\Exceptions\Storage as StorageException;

/**
 * @method static void staticPush(string $message);
 * @method static void staticSave();
 */
class Logger implements LoggerInterface
{
    use Singleton;

    /**
     * @var Storage|StorageInterface
     */
    protected readonly Storage|StorageInterface $storage;

    /**
     * @var array
     */
    private array $pushed = [];

    /**
     * @throws StorageException
     */
    protected function __construct(Storage $storage = null)
    {
        $this->storage = $storage ?: Storage::makeDiskFile('digua' . FileExtension::LOG->value);
    }

    /**
     * Add message to log.
     *
     * @inheritdoc
     */
    public function push(string $message): void
    {
        $this->pushed[] = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    }

    /**
     * Save log file.
     *
     * @return void
     * @throws StorageException
     */
    public function save(): void
    {
        $this->storage->write(implode("\n", $this->pushed) . "\n");
    }

    /**
     * Auto save log file.
     *
     * @throws StorageException
     */
    public function __destruct()
    {
        if (!empty($this->pushed)) {
            $this->save();
        }
    }
}