<?php declare(strict_types=1);

namespace Digua\Interfaces;

interface Logger
{
    /**
     * @param string $message
     * @return void
     */
    public function push(string $message): void;
}