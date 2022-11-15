<?php

namespace Framework\Response;

use Digua\Response;

class Fake extends Response
{
    /**
     * Location.
     *
     * @var string
     */
    public string $location;

    /**
     * Data.
     *
     * @var array
     */
    public array $json;

    /**
     * Data.
     *
     * @var string
     */
    public string $html;

    /**
     * @inheritdoc
     */
    public function location(string $url): void
    {
        $this->location = $url;
    }

    /**
     * @inheritdoc
     */
    public function json(mixed $data): void
    {
        $this->json = $data;
    }

    /**
     * @inheritdoc
     */
    public function html(string $data): void
    {
        $this->html = $data;
    }
}
