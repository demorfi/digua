<?php declare(strict_types=1);

namespace Digua;

use Digua\Traits\{Data, Configurable, DiskPath};
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;

class Config
{
    use Data, Configurable, DiskPath;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/config'
    ];

    /**
     * @param string $name Config name
     * @throws PathException
     */
    public function __construct(string $name)
    {
        self::throwIsBrokenDiskPath();
        $this->array = require(self::getDiskPath(Helper::filterFileName($name) . FileExtension::PHP->value));
    }
}
