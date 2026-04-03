<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], ['csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['authenticate', 'csrf']);

