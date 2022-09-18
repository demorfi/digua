<?php

namespace Digua\Abstracts;

use Digua\Request;
use Digua\Response;

abstract class Controller
{
    /**
     * Request.
     *
     * @var Request
     */
    protected Request $request;

    /**
     * Response.
     *
     * @var Response
     */
    protected Response $response;

    /**
     * Controller constructor.
     *
     * @param Response $response
     * @param Request  $request
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }
}
