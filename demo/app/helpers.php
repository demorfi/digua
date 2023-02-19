<?php declare(strict_types=1);

use Digua\Config;
use Digua\Exceptions\Path as PathException;

if (!function_exists('config')) {

    /**
     * Get config instance.
     *
     * @param string $name
     * @return Config
     * @throws PathException
     */
    function config(string $name): Config
    {
        return new Config($name);
    }
}
