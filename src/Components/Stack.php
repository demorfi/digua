<?php declare(strict_types=1);

namespace Digua\Components;

use Digua\Interfaces\{
    Storage as StorageInterface,
    Stack as StackInterface
};
use Digua\Exceptions\BadMethodCall as BadMethodCallException;
use Generator;

/**
 * @mixin Storage|StorageInterface
 */
class Stack implements StackInterface
{
    /**
     * @param Storage|StorageInterface $storage
     */
    public function __construct(private readonly Storage|StorageInterface $storage)
    {
    }

    /**
     * @param string $stack
     * @return array
     */
    private function unpack(string $stack): array
    {
        return !empty($stack) ? unserialize($stack) : [];
    }

    /**
     * @inheritdoc
     */
    public function push(mixed $data): bool
    {
        return $this->storage->rewrite(function ($stack) use ($data) {
            $stack   = $this->unpack($stack);
            $stack[] = $data;
            return serialize($stack);
        });
    }

    /**
     * @inheritdoc
     */
    public function pull(): mixed
    {
        $this->storage->rewrite(function ($stack) use (&$data) {
            $stack = $this->unpack($stack);
            $data  = sizeof($stack) < 1 ? false : array_pop($stack);
            return serialize($stack);
        });

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function shift(): mixed
    {
        $this->storage->rewrite(function ($stack) use (&$data) {
            $stack = $this->unpack($stack);
            $data  = sizeof($stack) < 1 ? false : array_shift($stack);
            return serialize($stack);
        });

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function read(): Generator|false
    {
        while (true) {
            $data = $this->pull();
            if (!$data) {
                return false;
            }

            yield $data;
        }
    }

    /**
     * @inheritdoc
     */
    public function readReverse(): Generator|false
    {
        while (true) {
            $data = $this->shift();
            if (!$data) {
                return false;
            }

            yield $data;
        }
    }

    /**
     * @inheritdoc
     */
    public function shadow(): Generator
    {
        $stack = $this->unpack($this->storage->read());
        foreach ($stack as $value) {
            yield $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function size(): int
    {
        return sizeof($this->unpack($this->storage->read()));
    }

    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!is_callable([$this->storage, $name])) {
            throw new BadMethodCallException('method ' . $name . ' does not exist!');
        }

        return $this->storage->$name(...$arguments);
    }
}