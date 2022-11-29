<?php

namespace Digua\Request;

use Digua\Traits\Data as DataTrait;
use Digua\Interfaces\NamedCollection as NamedCollectionInterface;

class Post implements NamedCollectionInterface
{
    use DataTrait;

    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}