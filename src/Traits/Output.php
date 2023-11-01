<?php declare(strict_types=1);

namespace Digua\Traits;

trait Output
{
    /**
     * @return bool
     */
    public function startBuffer(): bool
    {
        return ob_start();
    }

    /**
     * @return bool
     */
    public function stopBuffer(): bool
    {
        return ob_end_flush();
    }

    /**
     * @return string|false
     */
    public function flushBuffer(): string|false
    {
        return ob_get_clean();
    }

    /**
     * @return void
     */
    public function cleanBuffer(): void
    {
        ob_clean();
    }
}
