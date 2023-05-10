<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Response;
use Digua\Enums\Headers;
use Digua\Exceptions\{
    Path as PathException,
    Abort as AbortException
};
use Digua\Interfaces\{
    Request as RequestInterface,
    Route as RouteInterface,
    Guardian as GuardianInterface
};

class Error extends Base implements GuardianInterface
{
    /**
     * @var int
     */
    protected int $code = 404;

    /**
     * @var string
     */
    protected string $message;

    /**
     * @inheritdoc
     */
    public function __construct(protected RequestInterface $request)
    {
        parent::__construct($this->request);
        $this->message = Headers::from($this->code)->getText();

        $exception = $this->request->getException();
        if ($exception instanceof AbortException) {
            $this->code    = $exception->getCode();
            $this->message = $exception->getMessage() ?: Headers::from($this->code)->getText();
        }
    }

    /**
     * @inheritdoc
     */
    public function granted(RouteInterface $route): bool
    {
        return true;
    }

    /**
     * @return Response|array
     * @throws PathException
     */
    public function defaultAction(): Response|array
    {
        $isAsync = $this->request->getData()->query()->isAsync();
        return Response::create(
            $isAsync
                ? ['error' => $this->message]
                : $this->render('error', ['code' => $this->code, 'message' => $this->message])
        )->addHttpHeader(Headers::from($this->code));
    }
}