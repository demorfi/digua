<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Exceptions\BadMethodCall as BadMethodCallException;

class Event
{
    /**
     * @var array
     */
    protected array $params = [];

    /**
     * @var array
     */
    protected array $handlers = [];

    /**
     * @var ?mixed
     */
    protected mixed $previous = null;

    /**
     * @param array           $params
     * @param int|string|null $id
     */
    public function __construct(array $params = [], protected int|string|null $id = null)
    {
        foreach ($params as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * @param array           $params
     * @param int|string|null $id
     * @return static
     */
    public static function make(array $params = [], int|string|null $id = null): static
    {
        return new static($params, $id);
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     * @return void
     */
    public function __set(int|string $key, mixed $value): void
    {
        $this->params[$key] = $value;
    }

    /**
     * @param int|string $name
     * @return mixed
     */
    public function __get(int|string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    /**
     * @param mixed ...$arguments
     * @return mixed
     */
    public function __invoke(mixed ...$arguments): mixed
    {
        foreach ($this->handlers as $handler) {
            $this->previous = $handler($this, $this->previous, ...$arguments);
        }

        return $this->previous;
    }

    /**
     * @return int|string|null
     */
    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * @param callable $closure
     * @return void
     */
    public function addHandler(callable $closure): void
    {
        $this->handlers[] = $closure;
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        $closure = $this->__get($name);
        if (!is_callable($closure)) {
            throw new BadMethodCallException(sprintf('Closure (%s) does not exist!', $name));
        }

        return $closure(...$arguments);
    }
}