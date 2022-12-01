<?php declare(strict_types=1);

namespace Digua;

use Digua\Traits\{Data, StaticPath};
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;

class Config
{
    use Data, StaticPath;

    /**
     * @param string $name Config name
     * @throws PathException
     */
    public function __construct(string $name)
    {
        self::throwIsEmptyPath();
        $this->array = require(self::getPathToFile($name . FileExtension::PHP->value));
    }
}
