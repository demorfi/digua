<?php declare(strict_types=1);

namespace App\Controllers\Api;

use Digua\Controllers\Base as BaseController;

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
    public function storeAction(): array
    {
        return ['success' => true];
    }
}