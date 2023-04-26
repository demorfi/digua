<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Digua\Controllers\Error as ErrorController;
use Digua\Response;

class Error extends ErrorController
{
    /**
     * Default action.
     *
     * @return Response
     */
    public function defaultAction(): Response
    {
        return Response::create(['data' => ['error' => true]])
            ->addHeader('http', 'HTTP/1.1 404 Not Found', 404);
    }
}