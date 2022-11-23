<?php

namespace Digua;

use Digua\Enums\Env as EnvEnum;
use Throwable;
use Digua\Components\Storage\Logger as LoggerStorage;
use Digua\Exceptions\Base as BaseException;

class Env
{
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
     * @param EnvEnum $env
     * @return void
     */
    public static function setMode(EnvEnum $env): void
    {
        $isDev = EnvEnum::Dev === $env;
        ini_set('display_errors', (int)$isDev);
        ini_set('display_startup_errors', (int)$isDev);
        ini_set('log_errors', 1);
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
            (function ($code, $message, $file, $line) {
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
            })(...)
        );
    }

    /**
     * @return void
     */
    public static function addHandlerException(): void
    {
        set_exception_handler((fn(Throwable $exception) => LoggerStorage::staticPush($exception->__toString()))(...));
        Event::subscribe(
            'logger',
            BaseException::class,
            (function (Throwable $exception) {
                if (error_reporting() === E_ALL) {
                    LoggerStorage::staticPush('Notice: ' . $exception->__toString());
                }
            })(...)
        );
    }
}