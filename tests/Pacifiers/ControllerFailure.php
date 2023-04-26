<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Controllers\Base as BaseController;

class ControllerFailure extends BaseController
{
    /**
     * @return bool
     */
    #[GuardianAttribute(false)]
    public function failureAction(): bool
    {
        return false;
    }
}