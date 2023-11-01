<?php declare(strict_types=1);

namespace Digua\Components;

/**
 * Redefined function for testing load method.
 *
 * @param string $filePath
 * @return bool
 */
function is_readable(string $filePath): bool
{
    return !str_contains($filePath, 'unreadable') && is_file($filePath);
}

namespace Tests\Components;

use Digua\Components\DotEnv;
use Digua\Exceptions\File as FileException;
use PHPUnit\Framework\TestCase;

class DotEnvTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        file_put_contents(__DIR__ . '/.env-readable', 'ENV_KEY=ENV_VALUE');
        file_put_contents(__DIR__ . '/.env-unreadable', 'ENV_KEY=ENV_VALUE');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        unlink(__DIR__ . '/.env-readable');
        unlink(__DIR__ . '/.env-unreadable');
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testFileNotFound(): void
    {
        $filePath = __DIR__ . '/.env-not-found';
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File (' . $filePath . ') does not exist!');
        new DotEnv($filePath);
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testFileNotReadable(): void
    {
        $filePath = __DIR__ . '/.env-unreadable';
        $this->expectException(FileException::class);
        $this->expectExceptionMessage('File (' . $filePath . ') is unreadable!');

        // function is_readable redefined!
        (new DotEnv($filePath))->load();
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testLoadAndParsingFile(): void
    {
        $this->assertFalse(getenv('ENV_KEY'));

        // function is_readable redefined!
        (new DotEnv(__DIR__ . '/.env-readable'))->load();
        $this->assertSame('ENV_VALUE', getenv('ENV_KEY'));
    }
}