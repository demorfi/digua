<?php declare(strict_types=1);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once realpath(ROOT_PATH . '/../bootstrap.php');

$builder         = null;
$appEntryPath    = null;
$errorController = null;

if (str_starts_with($_SERVER['REQUEST_URI'], '/api/')) {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

    $request = new Digua\Request;
    $request->getData()->query()->exportFromPath(1);
    $builder         = new Digua\Routes\RouteAsNameBuilder($request);
    $appEntryPath    = '\App\Controllers\Api\\';
    $errorController = 'Error';
}

print (new Digua\RouteDispatcher())->default($builder, $appEntryPath, $errorController);