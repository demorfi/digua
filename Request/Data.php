<?php

namespace Digua\Request;

use Digua\Abstracts\Data as _Data;

class Data extends _Data
{
    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_POST);
    }
}
