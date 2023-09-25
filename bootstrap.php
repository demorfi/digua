<?php declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    throw new Exception('Root path is not defined!');
}

$loader = require_once __DIR__ . '/autoload.php';
$loader->addIncludePath(ROOT_PATH . '/../$1', __DIR__ . '/../$1/src');

Digua\Env::run();
Digua\Env::addHandlerError();
Digua\Env::addHandlerException();

return $loader;