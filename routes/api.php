<?php

declare(strict_types=1);

use App\Controllers\Api\TerritoryController;
use App\Controllers\Api\PublicTerritoryController;
use App\Controllers\Api\EnterpriseApiController;
use App\Controllers\Public\OnboardingController;

$router->get('/api/territorios/ufs', [TerritoryController::class, 'ufs'], ['authenticate']);
$router->get('/api/territorios/municipios', [TerritoryController::class, 'municipios'], ['authenticate']);
$router->get('/api/public/territorios/ufs', [PublicTerritoryController::class, 'ufs']);
$router->get('/api/public/territorios/municipios', [PublicTerritoryController::class, 'municipios']);
$router->post('/api/pagamentos/mercadopago/webhook', [OnboardingController::class, 'mercadoPagoWebhook']);
$router->get('/api/enterprise/executivo', [EnterpriseApiController::class, 'executiveSummary'], ['api.key']);
