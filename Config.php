<?php

namespace Digua;

use Digua\Traits\Data;
use Digua\Exceptions\Path as PathException;

class Config
{
    use Data;

    /**
     * Path to config files.
     *
     * @var string
     */
    public static string $path = '';

    /**
     * Config constructor.
     *
     * @param string $name Config name
     * @throws PathException
     */
    public function __construct(string $name)
    {
        if (empty(static::$path)) {
            throw new PathException('the path to the config is not configured');
        }

        $this->array = require(static::$path . $name . '.php');
    }

    /**
     * Set path to config files.
     *
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        static::$path = $path;
    }
}
