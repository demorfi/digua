<?php declare(strict_types=1);

namespace Tests;

use Digua\Response;
use Digua\Enums\{ContentType, Headers};
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsItPossibleToManageHeaders(): void
    {
        $response = new Response();
        $this->assertEmpty($response->getHeaders());

        $this->assertInstanceOf(
            $response::class,
            $response->addHeader('Content-Type', ContentType::JSON->value, 100)
        );

        $response->addHeader('Content-Type', ContentType::HTML->value, 200);
        $response->addHeader('Header-Other', 'test header string', 201);

        $this->assertIsArray($response->getHeaders());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::HTML->value, 200],
                'header-other' => ['Header-Other', 'test header string', 201]
            ],
            $response->getHeaders()
        );
    }

    /**
     * @return void
     */
    public function testIsItPossibleToAddRedirectHeader(): void
    {
        $response = new Response();
        $this->assertFalse($response->hasRedirect());

        $this->assertInstanceOf(
            $response::class,
            $response->redirectTo('https://url.test', 301)
        );

        $this->assertSame('https://url.test', $response->hasRedirect());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToAddHeaderByCode(): void
    {
        $response = new Response();
        $this->assertEmpty($response->getHeaders());

        $this->assertInstanceOf(
            $response::class,
            $response->addHttpHeader(Headers::UNPROCESSABLE_ENTITY)
        );

        $this->assertIsArray($response->getHeaders());
        $this->assertSame(['http' => ['http', 'HTTP/1.1 422 Unprocessable Entity', 422]], $response->getHeaders());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToAddDataContent(): void
    {
        $response = new Response();
        $response->setData(['key' => 'value']);
        $this->assertSame(ContentType::JSON, $response->getContentType());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::JSON->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
        $this->assertIsArray($response->getData());

        $response->setData('text data');
        $this->assertSame(ContentType::HTML, $response->getContentType());
        $this->assertEqualsCanonicalizing(
            [
                'content-type' => ['Content-Type', ContentType::HTML->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
        $this->assertIsString($response->getData());
    }

    /**
     * @return void
     */
    public function testIsItPossibleAliasesToAddDataContent(): void
    {
        $response = new Response();
        $this->assertInstanceOf(Response::class, $response->json(['key' => 'value']));
        $this->assertSame(ContentType::JSON, $response->getContentType());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::JSON->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
        $this->assertIsArray($response->getData());

        $this->assertInstanceOf(Response::class, $response->html('text data'));
        $this->assertSame(ContentType::HTML, $response->getContentType());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::HTML->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
        $this->assertIsString($response->getData());

        $this->assertInstanceOf(Response::class, $response->text('text data'));
        $this->assertSame(ContentType::HTML, $response->getContentType());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::HTML->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
        $this->assertIsString($response->getData());
    }

    /**
     * @return void
     */
    public function testIsItPossibleToCreateStaticInstance(): void
    {
        $response = Response::create(['key' => 'value']);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsArray($response->getData());
        $this->assertSame(ContentType::JSON, $response->getContentType());
        $this->assertSame(
            [
                'content-type' => ['Content-Type', ContentType::JSON->value . '; charset=UTF-8', 0]
            ],
            $response->getHeaders()
        );
    }

    /**
     * @return void
     */
    public function testIsItPossibleToPrintResponse(): void
    {
        $response = $this
            ->getMockBuilder(Response::class)
            ->onlyMethods(['sendHeader'])
            ->getMock();

        $headersSent = [];
        $response->method('sendHeader')
            ->will(
                $this->returnCallback(
                    static function (string $header, bool $replace = true, int $code = 0) use (&$headersSent) {
                        $headersSent[] = [$header, $replace, $code];
                    }
                )
            );

        $response->json(['key' => 'val']);
        $response->redirectTo('https://url.test', 301);
        $this->assertSame(json_encode(['key' => 'val']), (string)$response);
        $this->assertSame([
            ['Content-Type: application/json; charset=UTF-8', true, 0],
            ['Location: https://url.test', true, 301]
        ], $headersSent);

        $headersSent = [];
        $response->html('text content');
        $response->addHeader('Header-Other', 'test header string', 201);
        $response->addHeader('Location', 'https://url2.test', 302);
        $this->assertSame('text content', (string)$response);

        $this->assertSame([
            ['Content-Type: text/html; charset=UTF-8', true, 0],
            ['Location: https://url2.test', true, 302],
            ['Header-Other: test header string', true, 201]
        ], $headersSent);
    }
}