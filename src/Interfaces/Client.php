<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Client
{
    /**
     * @param string $url
     */
    public function setUrl(string $url): void;

    /**
     * @param string $name
     * @param string $value
     */
    public function addQuery(string $name, string $value): void;

    /**
     * @param string $name
     * @param string $value
     */
    public function addField(string $name, string $value): void;

    /**
     * Set client option.
     *
     * @param int   $name
     * @param mixed $value
     */
    public function setOption(int $name, mixed $value): void;

    /**
     * Get client option.
     *
     * @param int $name
     * @return mixed
     */
    public function getOption(int $name): mixed;

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return string
     */
    public function getResponse(): string;

    /**
     * @return int
     */
    public function getErrorCode(): int;

    /**
     * Send request.
     */
    public function send(): void;

    /**
     * Clean request.
     */
    public function clean(): void;
}
