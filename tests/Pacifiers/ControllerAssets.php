<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Controllers\Base as BaseController;

class ControllerAssets extends BaseController
{
    /**
     * @param ...$args
     * @return array
     */
    public function assetsAction(...$args): array
    {
        return $args;
    }

    /**
     * @param int $key
     * @return int
     */
    public function assetsIntAction(int $key): int
    {
        return $key;
    }

    /**
     * @param int    $key
     * @param string $value
     * @return array
     */
    public function assetsIntStringAction(int $key, string $value): array
    {
        return [$key, $value];
    }

    /**
     * @param StubService $service
     * @return StubService
     */
    public function assetsStubAction(StubService $service): StubService
    {
        return $service;
    }

    /**
     * @param int         $key
     * @param StubService $service
     * @return array
     */
    public function assetsStubMixedAction(int $key, StubService $service): array
    {
        return [$key, $service];
    }

    /**
     * @param int|string $key
     * @return void
     */
    public function assetsStubMultipleTypes(int|string $key): void
    {

    }
}