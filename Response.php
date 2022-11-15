<?php

namespace Digua;

class Response
{
    /**
     * Set location header.
     *
     * @param string $url Location URL
     */
    public function location(string $url): void
    {
        header('Location: ' . $url, true, 301);
    }

    /**
     * Print JSON.
     *
     * @param mixed $data Print data
     * @return void
     */
    public function json(mixed $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $json = json_encode($data);
        print ($json);
    }

    /**
     * Print HTML.
     *
     * @param string $data Print data
     */
    public function html(string $data): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        print ($data);
    }
}
