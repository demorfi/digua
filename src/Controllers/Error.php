<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Interfaces\Route;
use Digua\Response;
use Digua\Exceptions\Path as PathException;
use Digua\Interfaces\Guardian as GuardianInterface;

class Error extends Base implements GuardianInterface
{
    /**
     * @inheritdoc
     */
    public function granted(Route $route): bool
    {
        return true;
    }

    /**
     * @return Response|array
     * @throws PathException
     */
    public function defaultAction(): Response|array
    {
        $isAsync = $this->request->getData()->query()->isAsync();
        return Response::create($isAsync ? ['error' => 'not found'] : $this->render('error'))
            ->addHeader('http', 'HTTP/1.1 404 Not Found', 404);
    }
}