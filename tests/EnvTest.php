<?php declare(strict_types=1);

namespace Digua;

use Tests\EnvTest;

/**
 * Redefined function for testing addHandlerError method.
 *
 * @param callable $callable
 * @return callable
 */
function set_error_handler(callable $callable): callable
{
    return $callable;
}

/**
 * Redefined function for testing addHandlerException method.
 *
 * @param callable $callable
 * @return callable
 */
function set_exception_handler(callable $callable): callable
{
    return $callable;
}

/**
 * Disable output in console.
 *
 * @param string $string
 * @return string
 */
function printf(string $string): string
{
    EnvTest::$print = $string;
    return $string;
}

namespace Tests;

use Digua\Env;
use Digua\Components\Logger as LoggerStorage;
use Digua\Enums\Env as EnvEnum;
use Digua\Exceptions\{
    File as FileException,
    Base as BaseException
};
use PHPUnit\Framework\TestCase;

/**
 * @runInSeparateProcess
 */
class EnvTest extends TestCase
{
    /**
     * @var string
     */
    public static string $print = '';

    /**
     * @return array[]
     */
    protected function codeTypesProvider(): array
    {
        return $this->generateCodeTypesMap([
            ['Deprecated', 'E_DEPRECATED', EnvEnum::Prod, false],
            ['Notice', 'E_NOTICE'],
            ['Warning', 'E_WARNING'],
            ['Unknown', 'E_UNKNOWN'],
        ]);
    }

    /**
     * @return array[]
     */
    protected function userCodeTypesProvider(): array
    {
        return $this->generateCodeTypesMap([
            ['Error', 'E_USER_ERROR'],
            ['Deprecated', 'E_USER_DEPRECATED'],
            ['Notice', 'E_USER_NOTICE'],
            ['Warning', 'E_USER_WARNING']
        ]);
    }

    /**
     * @return array[]
     */
    protected function generateCodeTypesMap(array $codeTypes): array
    {
        $data = [];
        foreach (EnvEnum::cases() as $case) {
            foreach ($codeTypes as $codeType) {
                [$type, $const, $mode, $expect] = [...$codeType, null, null];
                $id        = sprintf('%s In %s mode', $const, $case->name);
                $expect    = !$expect && $case === $mode ? null : sprintf('%s: message %s in file:0', $type, $const);
                $data[$id] = [$case, defined((string)$const) ? constant((string)$const) : 1, 'message ' . $const, 'file', 0, $expect];
            }
        }

        return $data;
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', null);
        }

        Env::setDiskPath(__DIR__);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        if (is_file(__DIR__ . '/.env')) {
            unlink(__DIR__ . '/.env');
        }
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testRunDevMode(): void
    {
        Env::run();
        $this->assertFalse(Env::isDev());

        file_put_contents(__DIR__ . '/.env', 'APP_MODE=DEV');
        Env::run();
        $this->assertTrue(Env::isDev());
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testRunProdMode(): void
    {
        Env::dev();
        $this->assertTrue(Env::isDev());

        file_put_contents(__DIR__ . '/.env', 'APP_MODE=PROD');
        Env::run();
        $this->assertFalse(Env::isDev());
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testGetEnvValue(): void
    {
        file_put_contents(__DIR__ . '/.env', 'APP_TEST_ENV=VALUE_TEST');
        Env::run();
        $this->assertSame('VALUE_TEST', Env::get('APP_TEST_ENV'));
        $this->assertFalse(isset($_ENV['APP_TEST_ENV']));
        $this->assertSame('VALUE_TEST', getenv('APP_TEST_ENV'));
    }

    /**
     * @return void
     * @throws FileException
     */
    public function testGetEnvDefaultValue(): void
    {
        file_put_contents(__DIR__ . '/.env', 'APP_TEST_ENV=VALUE_TEST');
        Env::run();
        $this->assertFalse( Env::get('APP_TEST_DEFAULT'));
        $this->assertSame('DEFAULT_VALUE', Env::get('APP_TEST_DEFAULT', 'DEFAULT_VALUE'));
    }

    /**
     * @return void
     */
    public function testSetDevMode(): void
    {
        $this->assertFalse(Env::isDev());
        Env::dev();
        $this->assertTrue(Env::isDev());
    }

    /**
     * @return void
     */
    public function testSetProdMode(): void
    {
        Env::dev();
        $this->assertTrue(Env::isDev());

        Env::prod();
        $this->assertFalse(Env::isDev());
    }

    /**
     * @return void
     */
    public function testIsActiveDevMode(): void
    {
        $this->assertFalse(Env::isDev());
    }

    /**
     * @return void
     */
    public function testSetMode(): void
    {
        Env::setMode(EnvEnum::Dev);
        $this->assertTrue(Env::isDev());
        $this->assertSame('1', ini_get('display_errors'));
        $this->assertSame('1', ini_get('display_startup_errors'));
        $this->assertSame(E_ALL, error_reporting());

        Env::setMode(EnvEnum::Prod);
        $this->assertFalse(Env::isDev());
        $this->assertSame('0', ini_get('display_errors'));
        $this->assertSame('0', ini_get('display_startup_errors'));
        $this->assertSame((E_ALL & ~E_DEPRECATED & ~E_STRICT), error_reporting());
    }

    /**
     * @dataProvider codeTypesProvider
     * @dataProvider userCodeTypesProvider
     * @param EnvEnum $mode
     * @param int     $code
     * @param string  $message
     * @param string  $file
     * @param int     $line
     * @param ?string $expect
     * @return void
     */
    public function testAddHandlerError(EnvEnum $mode, int $code, string $message, string $file, int $line, ?string $expect): void
    {
        $input = $this->getMockBuilder(LoggerStorage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['push'])
            ->getMock();

        $input->expects(is_null($expect) ? $this->never() : $this->once())->method('push')
            ->willReturnCallback(function ($pushMessage) use ($expect) {
                $this->assertSame($pushMessage, $expect);
            });

        Env::setMode($mode);

        // function set_error_handler redefined inside!
        Env::addHandlerError($input)($code, $message, $file, $line);
    }

    /**
     * @return void
     */
    public function testBaseExceptionSubscribe(): void
    {
        $input = $this->getMockBuilder(LoggerStorage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['push'])
            ->getMock();

        $exception = new BaseException;
        $input->expects($this->once())->method('push')
            ->willReturnCallback(function ($pushMessage) use ($exception) {
                $this->assertSame($pushMessage, 'Notice: ' . $exception);
            });

        Env::setMode(EnvEnum::Dev);

        // function set_error_handler redefined inside!
        Env::addHandlerException($input);
        $exception->__construct();

        // logging only when dev mode is enabled
        Env::setMode(EnvEnum::Prod);
        $exception->__construct();
    }

    /**
     * @return void
     */
    public function testAddHandlerException(): void
    {
        $input = $this->getMockBuilder(LoggerStorage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['push'])
            ->getMock();

        $exception = new BaseException;
        $input->expects($this->once())->method('push')
            ->willReturnCallback(function ($pushMessage) use ($exception) {
                $this->assertSame($pushMessage, (string)$exception);
            });

        self::$print = '';
        Env::setMode(EnvEnum::Dev);

        // function set_error_handler redefined inside!
        Env::addHandlerException($input)($exception);
        $this->assertSame('<b>Fatal error</b>: Uncaught %s thrown in <b>%s</b> on line <b>%d</b>', self::$print);
    }
}