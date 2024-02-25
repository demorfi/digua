<?php declare(strict_types=1);

namespace Digua\Providers;

use Digua\Components\Types;
use Digua\Interfaces\{
    Provider as ProviderInterface,
    NamedCollection as NamedCollectionInterface
};

class NamedCollection implements ProviderInterface
{
    const TYPES = ['int', 'integer', 'float', 'string', 'bool', 'boolean'];

    /**
     * @param NamedCollectionInterface $collection
     */
    public function __construct(private readonly NamedCollectionInterface $collection)
    {
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasType(string $type): bool
    {
        return in_array($type, self::TYPES);
    }

    /**
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function get(string $key, string $type): mixed
    {
        $value = Types::value($this->collection->get($key));
        return $value->is($type) ? $value->getValue() : ($value->isNull() ? null : $value->to($type)->getValue());
    }
}