<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

try {
    $app->handleRequest(Request::capture());
} catch (\Throwable $e) {
    if (headers_sent()) {
        file_put_contents('php://stderr', "\n\n----- FATAL ERROR AFTER HEADERS SENT -----\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n-----------------------------------------\n\n");
    } else {
        throw $e;
    }
}
