<?php

namespace Digua\Components\Client;

use Digua\Traits\StaticPath;
use Digua\Interfaces\Client;
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;
use stdClass;

class Curl implements Client
{
    use StaticPath;

    /**
     * User agent.
     *
     * @var string
     */
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535';

    /**
     * Object instance.
     *
     * @var stdClass
     */
    protected stdClass $instance;

    /**
     * Curl constructor.
     *
     * @throws PathException
     */
    public function __construct()
    {
        self::isEmptyPath();

        $this->instance = new stdClass;

        $this->instance->curl     = curl_init();
        $this->instance->response = null;
        $this->instance->url      = null;
        $this->instance->fields   = [];
        $this->instance->query    = [];

        // Set default curl options
        curl_setopt($this->instance->curl, CURLOPT_HEADER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->instance->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->instance->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->instance->curl, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT);
        curl_setopt($this->instance->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->instance->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        if (is_resource($this->instance->curl)) {
            curl_close($this->instance->curl);
        }
    }

    /**
     * @inheritdoc
     */
    public function setUrl(string $url): void
    {
        $this->instance->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function addQuery(string $name, string $value): void
    {
        $this->instance->query[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function addField(string $name, string $value): void
    {
        $this->instance->fields[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setOption(string $name, mixed $value): void
    {
        curl_setopt($this->instance->curl, $name, $value);
    }

    /**
     * @inheritdoc
     */
    public function getOption(string $name): mixed
    {
        return curl_getinfo($this->instance->curl, $name);
    }

    /**
     * Use cookie.
     *
     * @param string $name
     */
    public function useCookie(string $name): void
    {
        $filePath = static::$path . $this->cleanFileName($name) . FileExtension::COOKIE->value;
        curl_setopt($this->instance->curl, CURLOPT_COOKIEJAR, $filePath);
        curl_setopt($this->instance->curl, CURLOPT_COOKIEFILE, $filePath);
    }

    /**
     * Get safe name file.
     *
     * @param string $fileName
     * @return string
     */
    protected function cleanFileName(string $fileName): string
    {
        return strtr(
            mb_convert_encoding($fileName, 'ASCII'),
            ' ,;:?*#!§$%&/(){}<>=`´|\\\'"',
            '____________________________'
        );
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        $url = $this->instance->url;
        if (!empty($this->instance->query)) {
            $url .= ((!str_contains($url, '?') ? '?' : '&') . http_build_query($this->instance->query));
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(): string
    {
        return $this->instance->response;
    }

    /**
     * @inheritdoc
     */
    public function send(): void
    {
        curl_setopt($this->instance->curl, CURLOPT_URL, str_replace(' ', '%20', $this->getUrl()));

        if (!empty($this->instance->fields)) {
            curl_setopt($this->instance->curl, CURLOPT_POST, true);
            curl_setopt($this->instance->curl, CURLOPT_POSTFIELDS, http_build_query($this->instance->fields));
        }

        $this->instance->response = curl_exec($this->instance->curl);
    }

    /**
     * @inheritdoc
     * @throws PathException
     */
    public function clean(): void
    {
        $this->__construct();
    }
}
