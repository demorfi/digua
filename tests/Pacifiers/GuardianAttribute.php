<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Attributes\Guardian;
use Digua\Interfaces\Route;
use Attribute;

#[Attribute]
class GuardianAttribute extends Guardian
{
    /**
     * @var int
     */
    public static int $called = 0;

    /**
     * @param bool $access
     */
    public function __construct(private readonly bool $access)
    {
        self::$called++;
    }

    /**
     * @param Route $route
     * @return bool
     */
    public function granted(Route $route): bool
    {
        return $this->access;
    }
}