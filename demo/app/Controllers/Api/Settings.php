<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Digua\Controllers\Base as BaseController;
use Digua\Response;
use Digua\Attributes\{Guardian\RequestPathRequired, Injector};
use Digua\Request;

class Settings extends BaseController
{
    /**
     * Default action.
     *
     * @return Response
     */
    public function defaultAction(): Response
    {
        return $this->response(['data' => ['result' => true]], 201);
    }

    /**
     * Store action.
     *
     * @param int     $userId
     * @param Request $request
     * @return array[]
     */
    #[RequestPathRequired('user')]
    #[Injector(['userId' => 'user'])]
    public function storeAction(int $userId, Request $request): array
    {
        return [
            'data' => [
                'userId' => $userId,
                'user'   => $request->getData()->query()->get('user')
            ]
        ];
    }
}