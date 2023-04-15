<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once realpath(ROOT_PATH . '/../bootstrap.php');

Digua\Env::prod();

$builder      = null;
$appEntryPath = null;

if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
    $request = new Digua\Request;
    $request->getData()->query()->exportFromPath(1);
    $builder      = new Digua\Routes\RouteAsNameBuilder($request);
    $appEntryPath = '\App\Controllers\Api\\';
}

print (new Digua\RouteDispatcher())->default($builder, $appEntryPath);