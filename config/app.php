<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'SIGERD'),
    'env' => env('APP_ENV', 'local'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'base_path' => env('APP_BASE_PATH', ''),
    'timezone' => env('APP_TIMEZONE', 'America/Fortaleza'),
    'version' => env('APP_VERSION', '1.0.0'),
    'middleware_aliases' => [
        'authenticate' => App\Middleware\Authenticate::class,
        'area.admin' => App\Middleware\CheckAdminArea::class,
        'area.operational' => App\Middleware\CheckOperationalArea::class,
        'operational.access' => App\Middleware\CheckOperationalAccess::class,
        'plancon.access' => App\Middleware\CheckPlanconAccess::class,
        'disaster.access' => App\Middleware\CheckDisasterExpansionAccess::class,
        'intelligence.access' => App\Middleware\CheckIntelligenceAccess::class,
        'documents.access' => App\Middleware\CheckDocumentsAccess::class,
        'governance.access' => App\Middleware\CheckGovernanceAccess::class,
        'advanced.reports.access' => App\Middleware\CheckAdvancedReportsAccess::class,
        'enterprise.access' => App\Middleware\CheckEnterpriseAccess::class,
        'api.key' => App\Middleware\AuthenticateApiKey::class,
        'csrf' => App\Middleware\VerifyCsrfToken::class,
    ],
];
