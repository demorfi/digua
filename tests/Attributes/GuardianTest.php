<?php declare(strict_types=1);

namespace Tests\Attributes;

use Digua\Attributes\Guardian;
use Digua\Interfaces\Guardian as GuardianInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GuardianTest extends TestCase
{
    /**
     * @return void
     */
    public function testInstanceOfGuardianInterface(): void
    {
        $guardian = $this->getMockBuilder(Guardian::class)->getMock();
        $this->assertInstanceOf(GuardianInterface::class, $guardian);
    }

    /**
     * @return void
     */
    public function testAttribute(): void
    {
        $guardian = $this->getMockBuilder(Guardian::class)->getMock();
        $reflection = new ReflectionClass($guardian);
        $this->assertEmpty($reflection->getAttributes());
    }
}