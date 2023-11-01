<?php declare(strict_types=1);

namespace Digua\Components\Client;

use CurlHandle;
use Digua\Traits\DiskPath;
use Digua\Helper;
use Digua\Interfaces\Client;
use Digua\Exceptions\Path as PathException;
use Digua\Enums\FileExtension;

class Curl implements Client
{
    use DiskPath;

    /**
     * @var string
     */
    const DEFAULT_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535 (KHTML, like Gecko) Chrome/14 Safari/535';

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH . '/storage'
    ];

    /**
     * @var CurlHandle|false
     */
    private CurlHandle|false $curl;

    /**
     * @var string
     */
    private string $response = '';

    /**
     * @var string
     */
    private string $url;

    /**
     * @var array
     */
    private array $fields = [];

    /**
     * @var array
     */
    private array $query = [];

    public function __construct(
        private array $options = [
            CURLOPT_HEADER         => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => self::DEFAULT_USER_AGENT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        ]
    ) {
        $this->curl = curl_init();
        curl_setopt_array($this->curl, $this->options); // Set default curl options
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
    public function getUrl(): string
    {
        return $this->url;
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
    public function getQuery(string $name): string|false
    {
        return $this->query[$name] ?? false;
    }

    /**
     * @inheritdoc
     */
    public function getUri(): string
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
    public function addField(string $name, string $value): void
    {
        $this->fields[$name] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getField(string $name): string|false
    {
        return $this->fields[$name] ?? false;
    }

    /**
     * @inheritdoc
     */
    public function setOption(int $name, mixed $value): void
    {
        $this->options[$name] = $value;
        curl_setopt($this->curl, $name, $value);
    }

    /**
     * @inheritdoc
     */
    public function getOption(int $name): mixed
    {
        return $this->options[$name] ?? false;
    }

    /**
     * @param string $fileName Cookie file name
     * @return void
     * @throws PathException
     */
    public function useCookie(string $fileName): void
    {
        self::throwIsBrokenDiskPath();
        $filePath = self::getDiskPath(Helper::filterFileName($fileName) . FileExtension::COOKIE->value);
        $this->setOption(CURLOPT_COOKIEJAR, $filePath);
        $this->setOption(CURLOPT_COOKIEFILE, $filePath);
    }

    /**
     * @inheritdoc
     */
    public function send(): void
    {
        $this->setOption(CURLOPT_URL, str_replace(' ', '%20', $this->getUri()));

        if (!empty($this->fields)) {
            $this->setOption(CURLOPT_POST, true);
            $this->setOption(CURLOPT_POSTFIELDS, http_build_query($this->fields));
        }

        $this->response = (string)curl_exec($this->curl);
    }

    /**
     * @inheritdoc
     */
    public function clean(): void
    {
        $this->__construct();
    }

    /**
     * @inheritdoc
     */
    public function getInfo(?int $option): mixed
    {
        return curl_getinfo($this->curl, $option);
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
    public function getErrorCode(): int
    {
        return curl_errno($this->curl);
    }
}
