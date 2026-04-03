<?php

declare(strict_types=1);

return [
    'cookie' => env('SESSION_COOKIE', 'sigerd_session'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'secure' => env('SESSION_SECURE', false),
    'http_only' => env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'Lax'),
];

