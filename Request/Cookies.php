<?php

namespace Digua\Request;

use Digua\Traits\Data;

class Cookies
{
    use Data;

    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
