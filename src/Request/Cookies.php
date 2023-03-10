<?php declare(strict_types=1);

namespace Digua\Request;

use Digua\Traits\Data as DataTrait;
use Digua\Interfaces\NamedCollection as NamedCollectionInterface;

class Cookies implements NamedCollectionInterface
{
    use DataTrait;

    public function __construct()
    {
        $this->array = (array)filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
