<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Request;
use Digua\Interfaces\Controller as ControllerInterface;

abstract class Base implements ControllerInterface
{
    /**
     * @param Request $request
     */
    public function __construct(protected Request $request)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }
}
