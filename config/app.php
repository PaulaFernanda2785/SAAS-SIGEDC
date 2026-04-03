<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'SIGERD'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'base_path' => env('APP_BASE_PATH', ''),
    'timezone' => env('APP_TIMEZONE', 'America/Fortaleza'),
    'middleware_aliases' => [
        'authenticate' => App\Middleware\Authenticate::class,
        'area.admin' => App\Middleware\CheckAdminArea::class,
        'area.operational' => App\Middleware\CheckOperationalArea::class,
        'csrf' => App\Middleware\VerifyCsrfToken::class,
    ],
];

