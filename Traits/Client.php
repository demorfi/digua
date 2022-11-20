<?php

namespace Digua\Traits;

use Digua\Interfaces\Client as ClientInterface;

trait Client
{
    /**
     * Send POST request.
     *
     * @param ClientInterface $client
     * @param string          $url
     * @param array           $fields
     * @return string
     */
    protected function sendPost(ClientInterface $client, string $url, array $fields = []): string
    {
        $client->setUrl($url);

        foreach ($fields as $name => $value) {
            $client->addField($name, $value);
        }

        $client->send();
        return $client->getResponse();
    }

    /**
     * Send GET request.
     *
     * @param ClientInterface $client
     * @param string          $url
     * @param array           $fields
     * @return string
     */
    protected function sendGet(ClientInterface $client, string $url, array $fields = []): string
    {
        $client->setUrl($url);

        foreach ($fields as $name => $value) {
            $client->addQuery($name, $value);
        }

        $client->send();
        return $client->getResponse();
    }
}
