<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_methods' => ['*'],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,

    'allowed_origins_patterns' => [],

    'exposed_headers' => [],

    'max_age' => 0,

];
