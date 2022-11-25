<?php declare(strict_types = 1);

namespace Digua\Controllers;

use Digua\Request;

class Base
{
    /**
     * @var bool
     */
    public bool $accessible = true;

    /**
     * @param Request $request
     */
    public function __construct(protected Request $request)
    {
    }
}
