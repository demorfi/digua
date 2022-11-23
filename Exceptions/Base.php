<?php declare(strict_types = 1);

namespace Digua\Exceptions;

use Exception;
use Throwable;
use Digua\Event;

class Base extends Exception
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     * @uses Event::register()
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Event::register([__CLASS__, get_called_class()], $this);
    }
}