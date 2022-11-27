<?php declare(strict_types=1);

namespace Digua;

use Digua\Enums\ContentType;
use Digua\Interfaces\Response as ResponseInterface;
use Stringable;

/**
 * @method json(mixed $data) Print JSON
 * @method html(mixed $data) Print HTML
 */
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
     * @inheritdoc
     */
    public function build(): self
    {
        if ($this->data instanceof ResponseInterface) {
            $this->data->build();
        }

        foreach ($this->headers as $header) {
            [$type, $value, $code] = $header;
            $header = match ($type) {
                'http' => $value,
                default => sprintf('%s: %s', ucfirst($type), $value)
            };

            header($header, true, $code);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->contentType === ContentType::HTML
            ? strval($this->data)
            : json_encode($this->data);
    }

    /**
     * @inheritdoc
     */
    public function __call(string $name, array $arguments): self
    {
        $this->setData($arguments[0]);
        return $this;
    }
}
