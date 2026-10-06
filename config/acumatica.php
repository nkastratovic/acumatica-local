<?php

return [
    'url' => env('ACUMATICA_URL'),

    'auth' => [
        'client_id' => env('ACUMATICA_CLIENT_ID'),
        'client_secret' => env('ACUMATICA_CLIENT_SECRET'),
        'username' => env('ACUMATICA_USERNAME'),
        'password' => env('ACUMATICA_PASSWORD'),
        'scope' => env('ACUMATICA_SCOPE', 'api offline_access'),
    ],

    'endpoints' => [
        'default' => [
            'name' => env('ACUMATICA_DEFAULT_ENDPOINT', 'Default'),
            'version' => env('ACUMATICA_DEFAULT_ENDPOINT_VERSION', '24.200.001'),
        ],

        'manufacturing' => [
            'name' => env('ACUMATICA_MANUFACTURING_ENDPOINT', 'MANUFACTURING'),
            'version' => env('ACUMATICA_MANUFACTURING_ENDPOINT_VERSION', '25.100.001'),
        ],
    ],

        'url' => env('ACUMATICA_URL'),

    'verify_ssl' => env('ACUMATICA_VERIFY_SSL', true),
];
