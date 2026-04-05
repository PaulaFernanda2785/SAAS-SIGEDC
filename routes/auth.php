<?php

declare(strict_types=1);

use App\Controllers\Auth\AuthController;

$router->get('/login', [AuthController::class, 'showLogin']);
$router->get('/acessar-plataforma', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], ['csrf']);
$router->post('/logout', [AuthController::class, 'logout'], ['authenticate', 'csrf']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword'], ['csrf']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword'], ['csrf']);
