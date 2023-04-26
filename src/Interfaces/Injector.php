<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Injector
{
    /**
     * @param object|string $class
     * @param string        $method
     */
    public function __construct(object|string $class, string $method);

    /**
     * @param Provider $provider
     * @return void
     */
    public function addProvider(Provider $provider): void;

    /**
     * @return array
     */
    public function inject(): array;
}