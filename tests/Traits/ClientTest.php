<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Components\Client\Curl;
use Digua\Traits\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Curl|MockObject
     */
    private Curl|MockObject $mockClient;

    /**
     * @var object
     */
    private object $traitClient;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        $this->traitClient = new class {
            use client {
                sendPost as public;
                sendGet as public;
            }
        };

        $this->mockClient = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['setUrl', 'addQuery', 'addField', 'send', 'getResponse'])
            ->getMock();

        $this->mockClient->expects($this->once())->method('setUrl')
            ->with('http://test-url.loc/');

        $this->mockClient->expects($this->once())->method('send');
        $this->mockClient->expects($this->once())->method('getResponse')
            ->will($this->returnValue('response text'));
    }

    /**
     * @return void
     */
    public function testSendPostMethod(): void
    {
        $names  = ['key1', 'key2'];
        $values = ['value1', 'value2'];

        $this->mockClient->expects($this->exactly(2))->method('addField')
            ->with(
                $this->callback(static function (string $name) use (&$names) {
                    return $name === array_shift($names);
                }),
                $this->callback(static function (string $value) use (&$values) {
                    return $value === array_shift($values);
                })
            );

        $result = $this->traitClient->sendPost(
            $this->mockClient,
            'http://test-url.loc/',
            ['key1' => 'value1', 'key2' => 'value2']
        );

        $this->assertSame($result, 'response text');
    }

    /**
     * @return void
     */
    public function testSendGetMethod(): void
    {
        $names  = ['key1', 'key2'];
        $values = ['value1', 'value2'];

        $this->mockClient->expects($this->exactly(2))->method('addQuery')
            ->with(
                $this->callback(static function (string $name) use (&$names) {
                    return $name === array_shift($names);
                }),
                $this->callback(static function (string $value) use (&$values) {
                    return $value === array_shift($values);
                })
            );

        $result = $this->traitClient->sendGet(
            $this->mockClient,
            'http://test-url.loc/',
            ['key1' => 'value1', 'key2' => 'value2']
        );

        $this->assertSame($result, 'response text');
    }
}