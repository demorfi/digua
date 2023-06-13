<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Storage
{
    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return ?string
     */
    public function read(): ?string;

    /**
     * @param string $data
     * @return bool
     */
    public function write(string $data): bool;

    /**
     * @param string|callable $data
     * @return bool
     */
    public function rewrite(string|callable $data): bool;

    /**
     * @return bool
     */
    public function free(): bool;

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool;

    /**
     * @return bool
     */
    public function hasEof(): bool;

    /**
     * @return bool
     */
    public function setEof(): bool;
}