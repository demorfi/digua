<?php declare(strict_types=1);

namespace Digua\Traits;

trait Buffering
{
    /**
     * @return bool
     */
    public function startBuffering(): bool
    {
        return ob_start();
    }

    /**
     * @return string
     */
    public function getBufferingContents(): string
    {
        return (string)ob_get_contents();
    }

    /**
     * @return bool
     */
    public function stopBuffering(): bool
    {
        return ob_end_clean();
    }

    /**
     * @return string
     */
    public function flushBuffering(): string
    {
        return (string)ob_get_clean();
    }

    /**
     * @return void
     */
    public function cleanBuffering(): void
    {
        ob_clean();
    }
}
