<?php declare(strict_types=1);

namespace Tests;

use Digua\Registry;
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
     * @return void
     */
    protected function setUp(): void
    {
        $this->service = new StubService;
        Registry::set(StubService::class, $this->service);
    }

    /**
     * @return void
     */
    public function testSetStubService(): void
    {
        $this->assertTrue(Registry::has(StubService::class));
    }

    /**
     * @return void
     * @throws RegistryException
     */
    public function testGetStubService(): void
    {
        $this->assertSame($this->service, Registry::get(StubService::class));
    }

    /**
     * @return void
     * @throws RegistryException
     */
    public function testTryingToGetInvalidKey(): void
    {
        $this->expectException(RegistryException::class);
        Registry::get('test');
    }
}