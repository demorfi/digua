<?php

namespace Digua\Request;

use Digua\Abstracts\Data;

class Cookies extends Data
{
    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
