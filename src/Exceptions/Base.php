<?php declare(strict_types=1);

namespace Digua\Exceptions;

use Digua\LateEvent;
use Digua\Interfaces\Exception as ExceptionInterface;
use Exception;
use Throwable;

class Base extends Exception implements ExceptionInterface
{
    /**
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     * @uses LateEvent::notify()
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        LateEvent::notify(get_called_class(), $this);
    }
}