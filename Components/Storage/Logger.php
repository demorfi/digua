<?php

namespace Digua\Components\Storage;

use Digua\Exceptions\{
    Path as PathException,
    Storage as StorageException
};
use Digua\Traits\Singleton;
use Digua\Storage;
use Digua\Enums\ContentType;
use Digua\Enums\FileExtension;

/**
 * @method static void staticPush()
 */
class Logger
{
    use Singleton;

    /**
     * Storage instance.
     *
     * @var Storage
     */
    protected readonly Storage $storage;

    /**
     * Initialize.
     *
     * @throws PathException
     * @throws StorageException
     */
    private function __construct()
    {
        $this->storage = new Storage('digua' . FileExtension::LOG->value, ContentType::TEXT);
    }

    /**
     * Add message to log.
     *
     * @param string $message
     * @return void
     */
    public function push(string $message): void
    {
        $this->storage->append('[' . date('Y-m-d H:m:s', time()) . '] ' . $message . "\r\n");
    }

    /**
     * Save log file.
     *
     * @return void
     * @throws StorageException
     */
    public function save(): void
    {
        $this->storage->save(false);
    }

    /**
     * Auto save log file.
     *
     * @throws StorageException
     */
    public function __destruct()
    {
        if (!empty($this->storage->getContent())) {
            $this->save();
        }
    }
}