<?php declare(strict_types=1);

namespace Digua;

use Digua\Enums\ContentType;
use Digua\Interfaces\Response as ResponseInterface;
use Stringable;

class Response implements ResponseInterface
{
    /**
     * Redirect location.
     *
     * @var ?string
     */
    protected ?string $redirectTo = null;

    /**
     * @var mixed
     */
    protected mixed $data = null;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var ContentType
     */
    protected ContentType $contentType = ContentType::HTML;

    /**
     * @inheritdoc
     */
    public function addHeader(string $type, string $value, int $code = 0): self
    {
        $this->headers[strtolower($type)] = [$type, $value, $code];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function redirectTo(string $url, int $code = 302): self
    {
        $this->redirectTo = $url;
        $this->addHeader('location', $url, $code);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasRedirect(): string|false
    {
        return $this->redirectTo ?: false;
    }

    /**
     * @inheritdoc
     */
    public function setData(mixed $data): void
    {
        $this->data        = $data;
        $this->contentType = match (true) {
            (is_scalar($this->data) || $this->data instanceof Stringable) => ContentType::HTML,
            default => ContentType::JSON
        };

        if (!($this->data instanceof ResponseInterface)) {
            $this->addHeader('Content-Type', $this->contentType->value . '; charset=UTF-8');
        }
    }

    /**
     * @inheritdoc
     */
    public function json(array $data): self
    {
        $this->setData($data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function html(string $content): self
    {
        $this->setData($content);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function text(string $content): self
    {
        $this->setData($content);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public static function create(mixed $data): self
    {
        $self = new self;
        $self->setData($data);
        return $self;
    }

    /**
     * Build headers.
     *
     * @return void
     */
    protected function build(): void
    {
        foreach ($this->headers as $header) {
            [$type, $value, $code] = $header;
            $header = match ($type) {
                'http' => $value,
                default => sprintf('%s: %s', ucfirst($type), $value)
            };

            $this->sendHeader($header, true, $code);
        }
    }

    /**
     * @param string $header
     * @param bool   $replace
     * @param int    $code
     * @return void
     * @uses header
     */
    protected function sendHeader(string $header, bool $replace = true, int $code = 0): void
    {
        header($header, $replace, $code);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        $this->build();
        return $this->contentType === ContentType::HTML
            ? strval($this->data)
            : json_encode($this->data);
    }
}
