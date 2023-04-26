<?php declare(strict_types=1);

namespace Digua\Providers;

use Digua\Registry as BaseRegistry;
use Digua\Interfaces\{
    Provider as ProviderInterface,
    Service as ServiceInterface
};
use Digua\Exceptions\Registry as RegistryException;

class Registry implements ProviderInterface
{
    /**
     * @param ?ServiceInterface ...$services
     */
    public function __construct(?ServiceInterface ...$services)
    {
        foreach ($services as $service) {
            BaseRegistry::set($service::class, $service);
        }
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return BaseRegistry::has($type);
    }

    /**
     * @param string $key
     * @param string $type
     * @return ServiceInterface
     * @throws RegistryException
     */
    public function get(string $key, string $type): ServiceInterface
    {
        return BaseRegistry::get($type);
    }
}