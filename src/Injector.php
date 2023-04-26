<?php declare(strict_types=1);

namespace Digua;

use Digua\Components\Types;
use Digua\Attributes\Injector as InjectorAttribute;
use Digua\Interfaces\{
    Provider as ProviderInterface,
    Injector as InjectorInterface
};
use Digua\Exceptions\Injector as InjectorException;
use ReflectionMethod;
use Generator;
use ReflectionException;
use ReflectionParameter;
use ReflectionAttribute;

class Injector implements InjectorInterface
{
    /**
     * @var ReflectionMethod
     */
    protected readonly ReflectionMethod $reflection;

    /**
     * @var array
     */
    private array $providers = [];

    /**
     * @@inheritdoc
     * @throws ReflectionException
     */
    public function __construct(object|string $class, string $method)
    {
        $this->reflection = new ReflectionMethod($class, $method);
    }

    /**
     * @inheritdoc
     */
    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param string              $message
     * @return void
     * @throws InjectorException
     */
    private function throw(ReflectionParameter $parameter, string $message): void
    {
        throw new InjectorException(
            sprintf(
                $message . ' for {%s->%s(%s $%s)}',
                $parameter->getDeclaringClass()->getName(),
                $this->reflection->getName(),
                $parameter->getType(),
                $parameter->getName()
            )
        );
    }

    /**
     * @param string $name
     * @param string $type
     * @return mixed
     */
    protected function search(string $name, string $type): mixed
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasType($type)) {
                $value = Types::value($provider->get($name, $type));
                if ($value->is($type)) {
                    return $value->getValue();
                }
            }
        }

        return null;
    }

    /**
     * @return Generator
     * @throws InjectorException
     * @throws ReflectionException
     */
    protected function parameters(): Generator
    {
        $attributes = $this->reflection->getAttributes(
            InjectorAttribute::class,
            ReflectionAttribute::IS_INSTANCEOF
        );

        $injected = [];
        foreach ($this->reflection->getParameters() as $parameter) {
            $name = $parameter->getName();

            // Polymorphic arguments
            foreach ($attributes as $attribute) {
                $name = $attribute->newInstance()->get($name) ?? $name;
            }

            $foundValue = yield $name => $parameter;
            $injected[] = match (true) {
                !is_null($foundValue) => $foundValue,
                $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                $parameter->allowsNull() => null,
                default => $this->throw($parameter, 'No matching value found')
            };
        }

        return $injected;
    }

    /**
     * @inheritdoc
     * @throws InjectorException
     * @throws ReflectionException
     */
    public function inject(): array
    {
        $parameters = $this->parameters();
        while ($parameters->valid()) {
            $parameter = $parameters->current();

            $type = (string)$parameter->getType();
            if (str_contains($type, '|')) {
                $this->throw($parameter, 'Injector does not support multiple types');
            }

            $parameters->send($this->search($parameters->key(), $type));
        }

        return $parameters->getReturn();
    }
}