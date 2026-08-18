<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'hr' => [
        'base_url' => env('HR_API_BASE_URL'),
        'token' => env('HR_API_TOKEN'),
        'cashiers_path' => env('HR_API_CASHIERS_PATH', '/api/v1/integration/cashiers'),
        'connect_timeout' => env('HR_API_CONNECT_TIMEOUT', 3),
        'timeout' => env('HR_API_TIMEOUT', 10),
        'cache_stale_after' => env('HR_CASHIER_CACHE_STALE_AFTER', 3600),
    ],

];
