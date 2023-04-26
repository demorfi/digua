<?php declare(strict_types=1);

namespace Digua\Routes;

use Digua\Components\Types;
use Digua\Interfaces\Provider as ProviderInterface;
use Digua\Request;

class RouteAsNameProvider implements ProviderInterface
{
    const TYPES = ['int', 'integer', 'float', 'string', 'bool', 'boolean'];

    /**
     * @param Request $request
     */
    public function __construct(private readonly Request $request)
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
        $value = Types::value($this->request->getData()->query()->get($key));
        return $value->is($type) ? $value->getValue() : ($value->isNull() ? null : $value->to($type)->getValue());
    }
}