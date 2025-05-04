<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../api/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../api/vendor/autoload.php';

(require_once __DIR__.'/../api/bootstrap/app.php')
    ->handleRequest(Request::capture());
