<?php

namespace Digua;

use Digua\Traits\{Data, StaticPath};
use Digua\Exceptions\Path as PathException;

class Config
{
    use Data, StaticPath;

    /**
     * Config constructor.
     *
     * @param string $name Config name
     * @throws PathException
     */
    public function __construct(string $name)
    {
        self::isEmptyPath();
        $this->array = require(static::$path . $name . '.php');
    }
}
