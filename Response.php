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
     * @param array $data Print data
     */
    public function json(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        print (json_encode($data));
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
