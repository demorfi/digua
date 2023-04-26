<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Digua\Controllers\Base as BaseController;
use Digua\Attributes\{Guardian\RequestPathRequired, Injector};
use Digua\Request;

class Settings extends BaseController
{
    /**
     * Default action.
     *
     * @return array[]
     */
    public function defaultAction(): array
    {
        return ['data' => ['result' => true]];
    }

    /**
     * Store action.
     *
     * @return true[]
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