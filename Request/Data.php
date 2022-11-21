<?php

namespace Digua\Request;

use Digua\Traits\Data as DataTrait;

class Data
{
    use DataTrait;

    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
