<?php

namespace Digua\Interfaces;

use Stringable;

interface Response extends Stringable
{
    /**
     * @param string $type  Header type
     * @param string $value Header value
     * @param int    $code  Response code
     * @return self
     */
    public function addHeader(string $type, string $value, int $code): self;

    /**
     * @return array
     */
    public function getHeaders(): array;

    /**
     * @param string $url
     * @param int    $code
     * @return self
     */
    public function redirectTo(string $url, int $code): self;

    /**
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
     * Create response instance.
     *
     * @param mixed $data
     * @return self
     */
    public static function create(mixed $data): self;

    /**
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
     * @param string $name
     * @param array  $arguments
     * @return self
     */
    public function __call(string $name, array $arguments): self;
}