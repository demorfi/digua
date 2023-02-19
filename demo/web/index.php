<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once realpath(ROOT_PATH . '/../bootstrap.php');
require_once ROOT_PATH . '/app/helpers.php';

Digua\Env::prod();
print (new Digua\RouteDispatcher())->default();