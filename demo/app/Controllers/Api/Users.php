<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Digua\Controllers\Resource as ResourceController;
use Digua\Attributes\Guardian\RequestPathAllowed;

class Users extends ResourceController
{
    /**
     * Default action.
     *
     * @return array[]
     */
    public function getDefaultAction(): array
    {
        return ['data' => ['users' => 1]];
    }

    /**
     * @param int $id
     * @return array[]
     */
    #[RequestPathAllowed('id')]
    public function getUserAction(int $id): array
    {
        return [
            'data' => [
                'userId' => $id
            ]
        ];
    }

    /**
     * @param int $id
     * @return array[]
     */
    #[RequestPathAllowed('id')]
    public function postUserAction(int $id): array
    {
        return [
            'data' => [
                'userId'  => $id,
                'updated' => true
            ]
        ];
    }
}