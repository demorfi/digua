<?php declare(strict_types=1);

namespace Digua\Interfaces;

use Digua\Interfaces\Request as RequestInterface;

interface RouteBuilder
{
    /**
     * @param Request $request
     */
    public function __construct(RequestInterface $request);

    /**
     * @return Request
     */
    public function request(): RequestInterface;

    /**
     * @return string|null
     */
    public function getControllerName(): ?string;

    /**
     * @return string|null
     */
    public function getActionName(): ?string;

    /**
     * @param string $controllerName
     * @param string $actionName
     * @return $this
     */
    public function forced(string $controllerName, string $actionName): static;

    /**
     * @param string $path
     * @param string $action
     * @return $this
     */
    public function get(string $path, string $action): static;

    /**
     * @param string $path
     * @return string
     */
    public function getAction(string $path): string;
}