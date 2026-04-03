<?php

declare(strict_types=1);

use App\Controllers\Operational\DashboardController;

$router->get('/operational', [DashboardController::class, 'index'], ['authenticate', 'area.operational']);

