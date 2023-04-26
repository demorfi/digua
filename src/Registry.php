<?php declare(strict_types=1);

namespace Digua;

use Digua\Interfaces\Service as ServiceInterface;
use Digua\Exceptions\Registry as RegistryException;

abstract class Registry
{
    /**
     * @var array
     */
    private static array $services = [];

    /**
     * @param string           $key
     * @param ServiceInterface $service
     * @return void
     */
    final public static function set(string $key, ServiceInterface $service): void
    {
        self::$services[$key] = $service;
    }

    /**
     * @param string $key
     * @return bool
     */
    final public static function has(string $key): bool
    {
        return isset(self::$services[$key]);
    }

    /**
     * @param string $key
     * @return ServiceInterface
     * @throws RegistryException
     */
    final public static function get(string $key): ServiceInterface
    {
        return self::$services[$key] ?? throw new RegistryException('Invalid key given');
    }
}