<?php declare(strict_types=1);

namespace Digua\Exceptions;

use ReflectionParameter;
use Throwable;

class Injector extends Base
{
    /**
     * @var ReflectionParameter
     */
    protected ReflectionParameter $parameter;

    /**
     * @param ReflectionParameter $parameter
     * @param int                 $code
     * @param string              $message
     * @param ?Throwable          $previous
     */
    public function __construct(ReflectionParameter $parameter, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $this->parameter = $parameter;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return ReflectionParameter
     */
    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }
}