<?php declare(strict_types=1);

namespace Tests\Providers;

use Digua\Providers\Registry;
use Digua\Exceptions\Registry as RegistryException;
use PHPUnit\Framework\TestCase;
use Tests\Pacifiers\StubService;

class RegistryTest extends TestCase
{
    /**
     * @var StubService
     */
    private StubService $service;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->service = new StubService;
        $this->registry = new Registry($this->service);
    }

    /**
     * @return void
     */
    public function testHasType(): void
    {
        $this->assertTrue($this->registry->hasType(StubService::class));
    }

    /**
     * @return void
     */
    public function testHasInvalidType(): void
    {
        $this->assertFalse($this->registry->hasType('foobar'));
    }

    /**
     * @return void
     * @throws RegistryException
     */
    public function testGetStubService(): void
    {
        $this->assertSame($this->service, $this->registry->get('', StubService::class));
    }

    /**
     * @return void
     * @throws RegistryException
     */
    public function testTryingToGetInvalidType(): void
    {
        $this->expectException(RegistryException::class);
        $this->registry->get('', 'foobar');
    }
}