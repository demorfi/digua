<?php declare(strict_types=1);

namespace Digua;

use Digua\Components\{DotEnv, Logger as LoggerStorage};
use Digua\Traits\DiskPath;
use Digua\Enums\Env as EnvEnum;
use Digua\Interfaces\Logger as LoggerInterface;
use Digua\Exceptions\{
    Base as BaseException,
    File as FileException
};
use Throwable;

class Env
{
    use DiskPath;

    /**
     * @var EnvEnum
     */
    private static EnvEnum $mode = EnvEnum::Prod;

    /**
     * @var string[]
     */
    protected static array $defaults = [
        'diskPath' => ROOT_PATH
    ];

    /**
     * @param string $fileName
     * @return void
     * @throws FileException
     */
    public static function run(string $fileName = '.env'): void
    {
        if (($filePath = self::getDiskPath($fileName)) && file_exists($filePath)) {
            (new DotEnv($filePath))->load();
        }

        self::setMode(
            str_starts_with(strtoupper((string)self::get('APP_MODE')), 'DEV')
                ? EnvEnum::Dev
                : EnvEnum::Prod
        );
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $name, mixed $default = false): mixed
    {
        $name = strtoupper($name);
        return $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name, true) ?: $default;
    }

    /**
     * @return void
     */
    public static function prod(): void
    {
        self::setMode(EnvEnum::Prod);
    }

    /**
     * @return void
     */
    public static function dev(): void
    {
        self::setMode(EnvEnum::Dev);
    }

    /**
     * @return bool
     */
    public static function isDev(): bool
    {
        return self::$mode === EnvEnum::Dev;
    }

    /**
     * @param EnvEnum $env
     * @return void
     */
    public static function setMode(EnvEnum $env): void
    {
        self::$mode = $env;
        $isDev = EnvEnum::Dev === $env;
        ini_set('display_errors', (int)$isDev);
        ini_set('display_startup_errors', (int)$isDev);
        error_reporting($isDev ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_STRICT));
    }

    /**
     * @param ?LoggerInterface $logger
     * @return ?callable
     */
    public static function addHandlerError(?LoggerInterface $logger = null): ?callable
    {
        $logger ??= LoggerStorage::getInstance();
        return set_error_handler(
            static function ($code, $message, $file, $line) use($logger) {
                if (!(error_reporting() & $code)) {
                    return false;
                }

                $type = match ($code) {
                    E_USER_ERROR => 'Error',
                    E_USER_DEPRECATED, E_DEPRECATED => 'Deprecated',
                    E_USER_NOTICE, E_NOTICE => 'Notice',
                    E_USER_WARNING, E_WARNING => 'Warning',
                    default => 'Unknown'
                };

                $logger->push($type . ': ' . $message . ' in ' . $file . ':' . $line);
                return false;
            }
        );
    }

    /**
     * @param ?LoggerInterface $logger
     * @return ?callable
     */
    public static function addHandlerException(?LoggerInterface $logger = null): ?callable
    {
        $logger ??= LoggerStorage::getInstance();

        // Subscribe all exception message
        LateEvent::subscribe(
            BaseException::class,
            function (Throwable $exception) use($logger) {
                    self::isDev() && $logger->push('Notice: ' . $exception);
            }
        );

        return set_exception_handler(
            function (Throwable $exception) use($logger) {
                $logger->push((string)$exception);
                if (self::isDev()) {
                    printf(
                        '<b>Fatal error</b>: Uncaught %s thrown in <b>%s</b> on line <b>%d</b>',
                        $exception,
                        $exception->getFile(),
                        $exception->getLine()
                    );
                }
            }
        );
    }
}