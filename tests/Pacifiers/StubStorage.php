<?php declare(strict_types=1);

namespace Tests\Pacifiers;

use Digua\Interfaces\Storage;

class StubStorage implements Storage
{
    /**
     * @var array
     */
    public array $arguments;

    /**
     * @param mixed ...$arguments
     */
    public function __construct(mixed ...$arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return __METHOD__;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return __METHOD__;
    }

    /**
     * @return ?string
     */
    public function read(): ?string
    {
        return __METHOD__;
    }

    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool
    {
        return true;
    }

    /**
     * @param callable|string $data
     * @return bool
     */
    public function rewrite(callable|string $data): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function free(): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function hasEof(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function setEof(): bool
    {
        return true;
    }
}