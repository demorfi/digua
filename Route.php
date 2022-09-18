<?php

namespace Digua;

class Route
{
    /**
     * Request instance.
     *
     * @var Request
     */
    private Request $request;

    /**
     * Route constructor.
     *
     * @param string|null $defName
     * @param string|null $defAction
     */
    public function __construct(string $defName = null, string $defAction = null)
    {
        $this->request = new Request();

        try {
            $name   = $defName ?: ('\Controllers\\' . ucfirst($this->request->getQuery()->getName()));
            $action = $defAction ?: ($this->request->getQuery()->getAction() . 'Action');

            if (!class_exists($name, true)) {
                throw new \Exception($name . ' - controller not found');
            }

            $controller = new $name($this->request, new Response);
            if (!method_exists($controller, $action)) {
                throw new \Exception($name . '->' . $action . ' - action not found');
            }

            call_user_func([$controller, $action]);
        } catch (\Exception $e) {
            header('HTTP/1.1 404 Not Found');
            print ($e->getMessage());
        }
    }
}
