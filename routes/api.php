<?php

declare(strict_types=1);

use App\Controllers\Api\TerritoryController;

$router->get('/api/territorios/ufs', [TerritoryController::class, 'ufs'], ['authenticate']);
$router->get('/api/territorios/municipios', [TerritoryController::class, 'municipios'], ['authenticate']);
