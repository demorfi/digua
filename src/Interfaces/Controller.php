<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Controller
{
    /**
     * @return string
     */
    public function getName(): string;
}