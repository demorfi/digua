<?php

namespace Digua\Abstracts;

use Digua\Request;

abstract class Controller
{
    /**
     * Controller constructor.
     *
     * @param Request $request
     */
    public function __construct(protected Request $request)
    {
    }
}
