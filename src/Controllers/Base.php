<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Exceptions\Path;
use Digua\Template;
use Digua\Interfaces\{
    Controller as ControllerInterface,
    Request as RequestInterface,
    RequestData as RequestDataInterface,
    Template as TemplateInterface
};

abstract class Base implements ControllerInterface, TemplateInterface
{
    /**
     * @param RequestInterface $request
     */
    public function __construct(protected RequestInterface $request)
    {
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * @inheritdoc
     * @throws Path
     */
    public function render(string $name, array $variables = []): Template
    {
        return ((new Template($this->request))->render($name, $variables));
    }

    /**
     * @return RequestInterface
     */
    public function request(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return RequestDataInterface
     */
    public function dataRequest(): RequestDataInterface
    {
        return $this->request->getData();
    }
}
