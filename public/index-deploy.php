<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode check
if (file_exists(__DIR__ . '/backend/storage/framework/maintenance.php')) {
    require __DIR__ . '/backend/storage/framework/maintenance.php';
}

// Autoloader
require __DIR__ . '/backend/vendor/autoload.php';

// Bootstrap the application
$app = require_once __DIR__ . '/backend/bootstrap/app.php';

// Handle the request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);
$response->send();

$kernel->terminate($request, $response);