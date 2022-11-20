<?php

namespace Digua\Traits;

trait Output
{
    /**
     * Start buffer.
     *
     * @return bool
     */
    public function startBuffer(): bool
    {
        return ob_start();
    }

    /**
     * Get buffer content.
     *
     * @return string
     */
    public function flushBuffer(): string
    {
        return ob_get_clean();
    }

    /**
     * Clean buffer.
     */
    public function cleanBuffer(): void
    {
        ob_clean();
    }
}
