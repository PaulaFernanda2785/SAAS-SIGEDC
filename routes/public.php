<?php

declare(strict_types=1);

use App\Controllers\Public\HomeController;
use App\Support\Response;

$router->get('/', [HomeController::class, 'index']);
$router->get('/planos', [HomeController::class, 'plans']);
$router->get('/demonstracao', [HomeController::class, 'demo']);
$router->get('/health', static fn () => Response::html('ok'));
