<?php

namespace Digua\Controllers;

use Digua\Request;

class Base
{
    /**
     * Accessible controller.
     *
     * @var bool
     */
    public bool $accessible = true;

    /**
     * Controller constructor.
     *
     * @param Request $request
     */
    public function __construct(protected Request $request)
    {
    }
}
