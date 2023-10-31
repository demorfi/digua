<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Traits\Singleton;

/**
 * @method static array staticMethodAsStatic(mixed ...$arguments)
 */
class StubSingleton
{
    use Singleton;

    /**
     * @param mixed ...$arguments
     * @return array
     */
    public function methodAsStatic(mixed ...$arguments): array
    {
        return $arguments;
    }
}