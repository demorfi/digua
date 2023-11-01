<?php declare(strict_types=1);

namespace Tests;

use Digua\Config;
use Digua\Exceptions\Path as PathException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }
    }

    /**
     * @return void
     */
    public function testThrowConfigPathIsNotReadable(): void
    {
        $this->expectException(PathException::class);
        $this->expectExceptionMessage('The disk path (' . __DIR__ . '/pathToFile) is not readable!');
        $this->expectExceptionCode(200);

        Config::setDiskPath(__DIR__ . '/pathToFile');
        new Config('pathToFile');
    }

    /**
     * @return void
     */
    public function testIsItPossibleLoadConfigFile(): void
    {
        Config::setDiskPath(__DIR__ . '/Pacifiers');

        $config = new Config('ArrayFile');
        $this->assertSame($config->getAll(), ['bool' => true, 'string' => '']);
    }
}