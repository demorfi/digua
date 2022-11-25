<?php declare(strict_types = 1);

namespace Digua\Controllers;

use Digua\Response;
use Digua\Template;

class Error extends Base
{
    /**
     * @inheritdoc
     */
    public bool $accessible = false;

    /**
     * @return Response
     */
    public function defaultAction(): Response
    {
        return Response::create((new Template)->render('error'))
            ->addHeader('http', 'HTTP/1.1 404 Not Found', 404);
    }
}