<?php declare(strict_types=1);

namespace Digua\Components\Client;

use CurlHandle;
use Digua\Traits\StaticPath;
use Digua\Interfaces\Client;
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;

class Curl implements Client
{
    use StaticPath;

    /**
     * @var string
     */
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535';

    /**
     * @var CurlHandle|false
     */
    private CurlHandle|false $curl;

    /**
     * @var string|null
     */
    private ?string $response = null;

    /**
     * @var string|null
     */
    private ?string $url = null;

    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @var array
     */
    private array $query = [];

    /**
     * @throws PathException
     */
    public function __construct()
    {
        self::throwIsBrokenPath();
        $this->curl = curl_init();

        // Set default curl options
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($this->curl, CURLOPT_USERAGENT, self::DEFAULT_USER_AGENT);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        if ($this->curl instanceof CurlHandle) {
            curl_close($this->curl);
        }
    }

    /**
     * @inheritdoc
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function addQuery(string $name, string $value): void
    {
        $this->query[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function addField(string $name, string $value): void
    {
        $this->fields[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setOption(int $name, mixed $value): void
    {
        curl_setopt($this->curl, $name, $value);
    }

    /**
     * @inheritdoc
     */
    public function getOption(int $name): mixed
    {
        return curl_getinfo($this->curl, $name);
    }

    /**
     * Use cookie file.
     *
     * @param string $fileName Cookie file name
     */
    public function useCookie(string $fileName): void
    {
        $filePath = self::getPathToFile($fileName . FileExtension::COOKIE->value);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $filePath);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $filePath);
    }

    /**
     * @inheritdoc
     */
    public function getUrl(): string
    {
        $url = $this->url;
        if (!empty($this->query)) {
            $url .= ((!str_contains($url, '?') ? '?' : '&') . http_build_query($this->query));
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @inheritdoc
     */
    public function send(): void
    {
        curl_setopt($this->curl, CURLOPT_URL, str_replace(' ', '%20', $this->getUrl()));

        if (!empty($this->fields)) {
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($this->fields));
        }

        $this->response = curl_exec($this->curl);
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
