<?php

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
     * @param string $name
     * @param mixed  $value
     */
    public function setOption(string $name, mixed $value): void;

    /**
     * Get client option.
     *
     * @param string $name
     * @return mixed
     */
    public function getOption(string $name): mixed;

    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return string
     */
    public function getResponse(): string;

    /**
     * Send request.
     */
    public function send(): void;

    /**
     * Clean request.
     */
    public function clean(): void;
}
