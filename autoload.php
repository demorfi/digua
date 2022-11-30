<?php declare(strict_types=1);

use Digua\Loader;

require_once __DIR__ . '/src/Loader.php';
$loader = new Loader;
$loader->register();
return $loader;