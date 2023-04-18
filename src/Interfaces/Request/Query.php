<?php declare(strict_types=1);

namespace Digua\Interfaces\Request;

interface Query
{
    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return string
     */
    public function getLocation(): string;

    /**
     * @return bool
     */
    public function isAsync(): bool;

    /**
     * @param int|string ...$variables
     * @return static
     */
    public function exportFromPath(int|string ...$variables): static;

    /**
     * @param int|string ...$variables
     * @return array|null
     */
    public function getFromPath(int|string ...$variables): array|null;

    /**
     * @param string ...$path
     * @return static
     */
    public function buildPath(string ...$path): static;
}