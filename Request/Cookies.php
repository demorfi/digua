<?php

namespace Digua\Request;

use Digua\Abstracts\Data as _Data;

class Cookies extends _Data
{
    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        $this->array = filter_input_array(INPUT_COOKIE);
    }
}
