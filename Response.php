<?php

namespace Digua;

use Digua\Enums\ContentType;
use Stringable;

/**
 * @method json(mixed $data) Print JSON
 * @method html(mixed $data) Print HTML
 */
class Response implements Stringable
{
    /**
     * Redirect location.
     *
     * @var ?string
     */
    protected ?string $redirectTo = null;

    /**
     * Data.
     *
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * Headers.
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * Content type.
     *
     * @var ContentType
     */
    protected ContentType $contentType = ContentType::HTML;

    /**
     * Add header.
     *
     * @param string $type  Header type
     * @param string $value Header value
     * @param int    $code  Response code
     * @return self
     */
    public function addHeader(string $type, string $value, int $code = 0): self
    {
        $this->headers[strtolower($type)] = [$type, $value, $code];
        return $this;
    }

    /**
     * Get headers list.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set redirect.
     *
     * @param string $url
     * @param int    $code
     * @return self
     */
    public function redirectTo(string $url, int $code = 302): self
    {
        $this->redirectTo = $url;
        $this->addHeader('location', $url, $code);
        return $this;
    }

    /**
     * Has redirect to.
     *
     * @return string|bool
     */
    public function hasRedirect(): string|bool
    {
        return $this->redirectTo ?: false;
    }

    /**
     * Set data content.
     *
     * @param mixed $data
     * @return void
     */
    protected function setData(mixed $data): void
    {
        $this->data        = $data;
        $this->contentType = match (true) {
            (is_scalar($this->data) || $this->data instanceof Stringable) => ContentType::HTML,
            default => ContentType::JSON
        };

        $this->addHeader('Content-Type', $this->contentType->value . '; charset=UTF-8');
    }

    /**
     * Get data content.
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Build response.
     *
     * @param mixed $data
     * @return self
     */
    public static function create(mixed $data): self
    {
        $self = new self;
        $self->setData($data);
        return $self;
    }

    /**
     * Output.
     *
     * @return self
     */
    public function build(): self
    {
        foreach ($this->headers as $header) {
            [$type, $value, $code] = $header;
            header(sprintf('%s: %s', ucfirst($type), $value), true, $code);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function __toString(): string
    {
        return $this->contentType === ContentType::HTML
            ? strval($this->data)
            : json_encode($this->data);
    }

    /**
     * Set data content.
     *
     * @param string $name      Method name
     * @param array  $arguments Method arguments
     * @return self
     */
    public function __call(string $name, array $arguments)
    {
        $this->setData(...$arguments);
        return $this;
    }
}
