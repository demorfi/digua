<?php declare(strict_types=1);

namespace Digua;

use Digua\Enums\Env as EnvEnum;
use Throwable;
use Digua\Components\Storage\Logger as LoggerStorage;
use Digua\Exceptions\Base as BaseException;

class Env
{
    /**
     * @var EnvEnum
     */
    private static EnvEnum $mode;

    /**
     * @return void
     */
    public static function prod(): void
    {
        self::setMode(self::$mode = EnvEnum::Prod);
    }

    /**
     * @return void
     */
    public static function dev(): void
    {
        self::setMode(self::$mode = EnvEnum::Dev);
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
        $isDev = EnvEnum::Dev === $env;
        ini_set('display_errors', (int)$isDev);
        ini_set('display_startup_errors', (int)$isDev);
        error_reporting($isDev ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_STRICT));

        self::addHandlerError();
        self::addHandlerException();
    }

    /**
     * @return void
     */
    public static function addHandlerError(): void
    {
        set_error_handler(
            function ($code, $message, $file, $line) {
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

                LoggerStorage::staticPush($type . ': ' . $message . ' in ' . $file . ':' . $line);
                return false;
            }
        );
    }

    /**
     * @return void
     */
    public static function addHandlerException(): void
    {
        set_exception_handler(
            (function (Throwable $exception) {
                LoggerStorage::staticPush((string)$exception);
                if (self::isDev()) {
                    printf(
                        '<b>Fatal error</b>: Uncaught %s thrown in <b>%s</b> on line <b>%d</b>',
                        $exception,
                        $exception->getFile(),
                        $exception->getLine()
                    );
                }
            })(...)
        );

        // Subscribe all exception message
        Event::subscribe(
            'logger',
            BaseException::class,
            (function (Throwable $exception) {
                    self::isDev() ?? LoggerStorage::staticPush('Notice: ' . $exception);
            })(...)
        );
    }
}