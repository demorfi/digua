<?php declare(strict_types=1);

namespace Digua\Controllers;

use Digua\Interfaces\Request as RequestInterface;

class Resource extends Base
{
    /**
     * @var string
     */
    protected readonly string $method;

    /**
     * @inheritdoc
     */
    public function __construct(protected RequestInterface $request)
    {
        parent::__construct($this->request);

        $this->method = $this->dataRequest()->query()->filtered()
            ->filteredVar(INPUT_SERVER, 'REQUEST_METHOD') ?? 'GET';
        $this->switchResAction($this->method);
    }

    /**
     * @param string $method
     * @return bool
     */
    protected function switchResAction(string $method): bool
    {
        $route  = $this->request->getRoute();
        $method = strtolower($method);
        $action = $method . ucfirst($route->getControllerAction());

        if (method_exists($this, $action)) {
            $route->switch($route->getBaseName(), $method . ucfirst($route->getBaseAction()));
            return true;
        }

        return false;
    }
}