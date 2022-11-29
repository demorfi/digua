<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Template
{
    /**
     * @param string $name
     * @param array  $variables
     * @return static
     */
    public function render(string $name, array $variables = []): Template;
}