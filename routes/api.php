<?php

declare(strict_types=1);

use App\Controllers\Api\TerritoryController;
use App\Controllers\Api\EnterpriseApiController;

$router->get('/api/territorios/ufs', [TerritoryController::class, 'ufs'], ['authenticate']);
$router->get('/api/territorios/municipios', [TerritoryController::class, 'municipios'], ['authenticate']);
$router->get('/api/enterprise/executivo', [EnterpriseApiController::class, 'executiveSummary'], ['api.key']);
