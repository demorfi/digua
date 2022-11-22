<?php

namespace Digua\Exceptions;

use Exception;
use Throwable;
use Digua\Event;

class Base extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Event::register([__CLASS__, get_called_class()], $this);
    }
}