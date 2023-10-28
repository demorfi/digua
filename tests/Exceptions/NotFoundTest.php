<?php declare(strict_types=1);

namespace Tests\Exceptions;

use Digua\Exceptions\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    /**
     * @return void
     * @throws NotFound
     */
    public function testUsesDefaultCode(): void
    {
        $this->expectExceptionCode(404);
        throw new NotFound;
    }
}