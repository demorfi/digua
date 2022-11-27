<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Route
{
    /**
     * @return string
     */
    public function getControllerName(): string;

    /**
     * @return string
     */
    public function getControllerAction(): string;

    /**
     * Switch active controller.
     *
     * @param string $controller Controller name
     * @param string $action     Controller action name
     * @return void
     */
    public function switch(string $controller, string $action): void;
}