<?php

declare(strict_types=1);

use App\Controllers\Operational\DashboardController;
use App\Controllers\Operational\IncidentController;
use App\Controllers\Operational\ReportController;

$operationalMiddleware = ['authenticate', 'area.operational', 'operational.access'];

$router->get('/operational', [DashboardController::class, 'index'], $operationalMiddleware);

$router->get('/operational/incidentes', [IncidentController::class, 'index'], $operationalMiddleware);
$router->post('/operational/incidentes', [IncidentController::class, 'storeIncident'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/briefing', [IncidentController::class, 'storeBriefing'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/comando', [IncidentController::class, 'upsertCommand'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/periodos', [IncidentController::class, 'storePeriod'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/registros', [IncidentController::class, 'storeRecord'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);

$router->get('/operational/relatorios/basico', [ReportController::class, 'basic'], $operationalMiddleware);
