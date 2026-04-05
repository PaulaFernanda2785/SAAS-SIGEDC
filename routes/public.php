<?php

declare(strict_types=1);

use App\Controllers\Public\HomeController;
use App\Controllers\Public\OnboardingController;
use App\Support\Response;

$router->get('/', [HomeController::class, 'index']);
$router->get('/planos', [HomeController::class, 'plans']);
$router->get('/demonstracao', [OnboardingController::class, 'showDemo']);
$router->post('/demonstracao/trial', [OnboardingController::class, 'startTrial'], ['csrf']);
$router->post('/demonstracao/assinar', [OnboardingController::class, 'startSubscription'], ['csrf']);
$router->get('/checkout', [OnboardingController::class, 'checkout']);
$router->post('/checkout/confirmar', [OnboardingController::class, 'confirmCheckout'], ['csrf']);
$router->get('/health', static fn () => Response::html('ok'));
