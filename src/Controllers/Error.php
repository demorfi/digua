<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Response;
use Digua\Exceptions\Path as PathException;

class Error extends Base
{
    /**
     * @return Response
     * @throws PathException
     */
    public function defaultAction(): Response
    {
        return Response::create($this->render('error'))
            ->addHeader('http', 'HTTP/1.1 404 Not Found', 404);
    }
}