<?php

namespace Digua\Request;

use Digua\Abstracts\Data as DataAbstract;

class Data extends DataAbstract
{
    /**
     * Data constructor.
     */
    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
