<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Client
{
    /**
     * @param string $url
     */
    public function setUrl(string $url): void;

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @param string $name
     * @param string $value
     */
    public function addQuery(string $name, string $value): void;

    /**
     * @param string $name
     * @return string|false
     */
    public function getQuery(string $name): string|false;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $name
     * @param string $value
     */
    public function addField(string $name, string $value): void;

    /**
     * @param string $name
     * @return string|false
     */
    public function getField(string $name): string|false;

    /**
     * @param int   $name
     * @param mixed $value
     */
    public function setOption(int $name, mixed $value): void;

    /**
     * @param int $name
     */
    public function getOption(int $name): mixed;

    /**
     * Send request.
     */
    public function send(): void;

    /**
     * Clean request.
     */
    public function clean(): void;

    /**
     * @param ?int $option
     * @return mixed
     */
    public function getInfo(?int $option): mixed;

    /**
     * @return string
     */
    public function getResponse(): string;

    /**
     * @return int
     */
    public function getErrorCode(): int;
}
