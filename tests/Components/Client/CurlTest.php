<?php declare(strict_types=1);

namespace Tests\Components\Client;

use Digua\Components\Client\Curl;
use Digua\Exceptions\Path as PathException;
use PHPUnit\Framework\TestCase;

class CurlTest extends TestCase
{
    /**
     * @var string
     */
    private string $cookieFileName = 'curl-test-cookie';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        Curl::setDiskPath(__DIR__);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $filePath = __DIR__ . '/' . $this->cookieFileName . '.cookie';
        is_file($filePath) && unlink($filePath);
    }

    /**
     * @return void
     */
    public function testIsItPossibleReplaceDefaultOptions(): void
    {
        $curl = new Curl;
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 20);

        $curl = new Curl([CURLOPT_TIMEOUT => 5]);
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 5);
    }

    /**
     * @return void
     */
    public function testSetAndGetUrl(): void
    {
        $curl = new Curl;
        $curl->setUrl('http://test-url.loc/');
        $this->assertSame($curl->getUrl(), 'http://test-url.loc/');
    }

    /**
     * @return void
     */
    public function testIsAddQueryAndIsReturnAddedQuery(): void
    {
        $curl = new Curl;
        $curl->addQuery('key1', 'value1');
        $curl->addQuery('key2', 'value2');

        $this->assertSame($curl->getQuery('key1'), 'value1');
        $this->assertSame($curl->getQuery('key2'), 'value2');
    }

    /**
     * @return void
     */
    public function testIsReturnCorrectUri(): void
    {
        $curl = new Curl;

        $curl->setUrl('http://test-url.loc/');
        $this->assertSame($curl->getUri(), 'http://test-url.loc/');

        $curl->addQuery('key1', 'value1');
        $this->assertSame($curl->getUri(), 'http://test-url.loc/?key1=value1');

        $curl->addQuery('key2', 'value2');
        $this->assertSame($curl->getUri(), 'http://test-url.loc/?key1=value1&key2=value2');

        $curl->setUrl('http://test-url.loc/?key0=value0');
        $this->assertSame($curl->getUri(), 'http://test-url.loc/?key0=value0&key1=value1&key2=value2');
    }

    /**
     * @return void
     */
    public function testIsAddFieldAndIsReturnAddedField(): void
    {
        $curl = new Curl;
        $curl->addField('key1', 'value1');
        $curl->addField('key2', 'value2');

        $this->assertSame($curl->getField('key1'), 'value1');
        $this->assertSame($curl->getField('key2'), 'value2');
    }

    /**
     * @return void
     */
    public function testIsSetAndGetCurlOption(): void
    {
        $curl = new Curl;

        $curl->setOption(CURLOPT_TIMEOUT, 5);
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 5);

        $curl->setOption(CURLOPT_TIMEOUT, 25);
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 25);
    }

    /**
     * @return void
     * @throws PathException
     */
    public function testIsItPossibleUseCookies(): void
    {
        $curl = new Curl;
        $curl->useCookie($this->cookieFileName);

        $this->assertTrue(str_ends_with($curl->getOption(CURLOPT_COOKIEJAR), $this->cookieFileName . '.cookie'));
        $this->assertTrue(str_ends_with($curl->getOption(CURLOPT_COOKIEFILE), $this->cookieFileName . '.cookie'));
    }

    /**
     * @return Curl
     */
    public function testSendRequestAndGetResponse(): Curl
    {
        $curl = new Curl;
        $curl->setUrl('https://www.google.com/search');
        $curl->addQuery('q', 'search test');
        $curl->send();

        $this->assertSame($curl->getOption(CURLOPT_URL), 'https://www.google.com/search?q=search+test');
        $this->assertSame($curl->getErrorCode(), 0);
        $this->assertTrue(str_contains($curl->getResponse(), 'value="search test"'));
        return $curl;
    }

    /**
     * @return void
     */
    public function testSendPostRequestAndGetResponse(): void
    {
        $curl = new Curl;
        $curl->setUrl('https://www.google.com/search');
        $curl->addField('q', 'search test');
        $curl->send();

        $this->assertSame($curl->getOption(CURLOPT_URL), 'https://www.google.com/search');
        $this->assertTrue($curl->getOption(CURLOPT_POST));
        $this->assertSame($curl->getOption(CURLOPT_POSTFIELDS), 'q=search+test');

        $this->assertSame($curl->getErrorCode(), 0);
        $this->assertTrue(str_contains($curl->getResponse(), 'Method Not Allowed'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleCleanCurlInstance(): void
    {
        $curl = new Curl();
        $curl->setOption(CURLOPT_TIMEOUT, 5);
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 5);

        $curl->clean();
        $this->assertSame($curl->getOption(CURLOPT_TIMEOUT), 20);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetInfoForLastRequest(): void
    {
        $curl = $this->testSendRequestAndGetResponse();
        $this->assertSame($curl->getInfo(CURLINFO_EFFECTIVE_URL), 'https://www.google.com/search?q=search+test');
        $this->assertSame($curl->getInfo(CURLINFO_HTTP_CODE), 200);
    }
}