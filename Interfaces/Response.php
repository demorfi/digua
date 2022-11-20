<?php

namespace Digua\Interfaces;

use Stringable;

interface Response extends Stringable
{
    /**
     * Add header.
     *
     * @param string $type  Header type
     * @param string $value Header value
     * @param int    $code  Response code
     * @return self
     */
    public function addHeader(string $type, string $value, int $code): self;

    /**
     * Get headers list.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Set redirect.
     *
     * @param string $url
     * @param int    $code
     * @return self
     */
    public function redirectTo(string $url, int $code): self;

    /**
     * Has redirect to.
     *
     * @return string|false
     */
    public function hasRedirect(): string|false;

    /**
     * Set data content.
     *
     * @param mixed $data
     * @return void
     */
    public function setData(mixed $data): void;

    /**
     * Get data content.
     *
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * Build response.
     *
     * @param mixed $data
     * @return self
     */
    public static function create(mixed $data): self;

    /**
     * Output.
     *
     * @return self
     */
    public function build(): self;

    /**
     * @inheritdoc
     * @return string
     */
    public function __toString(): string;

    /**
     * Set data content.
     *
     * @param string $name      Method name
     * @param array  $arguments Method arguments
     * @return self
     */
    public function __call(string $name, array $arguments): self;
}