<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Controllers\Base as BaseController;

class ControllerSuccess extends BaseController
{
    /**
     * @return bool
     */
    #[GuardianAttribute(true)]
    public function successAction(): bool
    {
        return true;
    }
}