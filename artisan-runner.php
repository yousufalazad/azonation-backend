<?php
// artisan-runner.php (TEMPORARY SCRIPT)
$password = 'secure-token-here'; // Set a secure password to protect access

if ($_GET['key'] !== $password) {
    http_response_code(403);
    exit('Unauthorized');
}

chdir(__DIR__);
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";

$commands = [
    'config:cache',
    'route:cache',
    'view:cache',
    'migrate --force',
];

foreach ($commands as $command) {
    echo "\n> php artisan {$command}\n";
    $kernel->call($command);
    echo $kernel->output();
}

echo "</pre>";