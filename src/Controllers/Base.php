<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Exceptions\Path;
use Digua\{Request, Template};
use Digua\Interfaces\{
    Controller as ControllerInterface,
    RequestData as RequestDataInterface,
    Template as TemplateInterface
};

abstract class Base implements ControllerInterface, TemplateInterface
{
    /**
     * @param Request $request
     */
    public function __construct(protected Request $request)
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
    public function render(string $name, array $variables = []): TemplateInterface
    {
        return ((new Template($this->request))->render($name, $variables));
    }

    /**
     * @return Request
     */
    public function request(): Request
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
