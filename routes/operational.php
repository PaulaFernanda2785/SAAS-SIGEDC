<?php

declare(strict_types=1);

use App\Controllers\Operational\DashboardController;
use App\Controllers\Operational\DisasterController;
use App\Controllers\Operational\IncidentController;
use App\Controllers\Operational\PlanconController;
use App\Controllers\Operational\ReportController;

$operationalMiddleware = ['authenticate', 'area.operational', 'operational.access'];
$planconMiddleware = ['authenticate', 'area.operational', 'operational.access', 'plancon.access'];
$disasterMiddleware = ['authenticate', 'area.operational', 'operational.access', 'disaster.access'];

$router->get('/operational', [DashboardController::class, 'index'], $operationalMiddleware);

$router->get('/operational/incidentes', [IncidentController::class, 'index'], $operationalMiddleware);
$router->post('/operational/incidentes', [IncidentController::class, 'storeIncident'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/briefing', [IncidentController::class, 'storeBriefing'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/comando', [IncidentController::class, 'upsertCommand'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/periodos', [IncidentController::class, 'storePeriod'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);
$router->post('/operational/incidentes/registros', [IncidentController::class, 'storeRecord'], ['authenticate', 'area.operational', 'operational.access', 'csrf']);

$router->get('/operational/plancon', [PlanconController::class, 'index'], $planconMiddleware);
$router->post('/operational/plancon', [PlanconController::class, 'storePlancon'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);
$router->post('/operational/plancon/riscos', [PlanconController::class, 'storeRisk'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);
$router->post('/operational/plancon/cenarios', [PlanconController::class, 'storeScenario'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);
$router->post('/operational/plancon/ativacao', [PlanconController::class, 'storeActivationLevel'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);
$router->post('/operational/plancon/recursos', [PlanconController::class, 'storeResource'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);
$router->post('/operational/plancon/revisoes', [PlanconController::class, 'storeReview'], ['authenticate', 'area.operational', 'operational.access', 'plancon.access', 'csrf']);

$router->get('/operational/desastres', [DisasterController::class, 'index'], $disasterMiddleware);
$router->post('/operational/desastres/pai', [DisasterController::class, 'storePai'], ['authenticate', 'area.operational', 'operational.access', 'disaster.access', 'csrf']);
$router->post('/operational/desastres/operacoes', [DisasterController::class, 'storeOperation'], ['authenticate', 'area.operational', 'operational.access', 'disaster.access', 'csrf']);
$router->post('/operational/desastres/planejamento', [DisasterController::class, 'storePlanning'], ['authenticate', 'area.operational', 'operational.access', 'disaster.access', 'csrf']);
$router->post('/operational/desastres/seguranca', [DisasterController::class, 'storeSafety'], ['authenticate', 'area.operational', 'operational.access', 'disaster.access', 'csrf']);
$router->post('/operational/desastres/desmobilizacao', [DisasterController::class, 'storeDemobilization'], ['authenticate', 'area.operational', 'operational.access', 'disaster.access', 'csrf']);

$router->get('/operational/relatorios/basico', [ReportController::class, 'basic'], $operationalMiddleware);
