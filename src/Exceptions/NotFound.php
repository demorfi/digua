<?php declare(strict_types=1);

namespace Digua\Exceptions;

class NotFound extends Abort
{
    /**
     * @var int
     */
    protected $code = 404;
}