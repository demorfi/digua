<?php declare(strict_types=1);

namespace Digua\Interfaces\Template;

interface Engine
{
    /**
     * @param string $template
     * @param array  $variables
     * @return string
     */
    public function build(string $template, array $variables = []): string;
}