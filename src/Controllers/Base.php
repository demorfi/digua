<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Interfaces\{
    Controller as ControllerInterface,
    Request as RequestInterface,
    Request\Data as RequestDataInterface,
    Template as TemplateInterface
};
use Digua\Enums\Headers;
use Digua\{Template, Response};
use Digua\Exceptions\{Path, NotFound, Abort};

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
     * @param mixed       $data
     * @param int|Headers $code
     * @return Response
     */
    public function response(mixed $data, int|Headers $code = 200): Response
    {
        return Response::create($data)->addHttpHeader(($code instanceof Headers) ? $code : Headers::from($code));
    }

    /**
     * @return RequestDataInterface
     */
    public function dataRequest(): RequestDataInterface
    {
        return $this->request->getData();
    }

    /**
     * @param string $message
     * @return void
     * @throws NotFound
     */
    public function throwNotFound(string $message = ''): void
    {
        throw new NotFound($message);
    }

    /**
     * @param int|Headers $code
     * @param string      $message
     * @return void
     * @throws Abort
     */
    public function throwAbort(int|Headers $code = 0, string $message = ''): void
    {
        throw new Abort($message, ($code instanceof Headers) ? $code->value : $code);
    }
}
