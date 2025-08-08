<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login'],
    'allowed_methods' => ['*'],
    'allowed_origins' =>  ['https://my.azonation.com', 'http://localhost:5173'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Allow cookies (needed for authentication)
];
