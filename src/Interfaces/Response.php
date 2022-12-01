<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Stringable;
use Digua\Enums\ContentType;

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
     * @return ContentType
     */
    public function getContentType(): ContentType;

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
     * @param array $data
     * @return self
     */
    public function json(array $data): self;

    /**
     * @param string $content
     * @return self
     */
    public function html(string $content): self;

    /**
     * @param string $content
     * @return self
     */
    public function text(string $content): self;

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
}