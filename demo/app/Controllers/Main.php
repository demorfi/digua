<?php declare(strict_types=1);

namespace App\Controllers;

use Digua\{Template, Helper};
use Digua\Exceptions\Path as PathException;
use Digua\Controllers\Base as BaseController;

class Main extends BaseController
{
    /**
     * Default action.
     *
     * @return Template|array
     * @throws PathException
     */
    public function defaultAction(): Template|array
    {
        $title = 'Main Page';

        if ($this->dataRequest()->post()->has('check')) {
            return ['success' => true, 'output' => Helper::config('base')->getAll()];
        }

        return $this->render('main', compact('title'));
    }
}
