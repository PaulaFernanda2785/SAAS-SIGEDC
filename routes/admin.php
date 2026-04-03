<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;

$router->get('/admin', [DashboardController::class, 'index'], ['authenticate', 'area.admin']);

