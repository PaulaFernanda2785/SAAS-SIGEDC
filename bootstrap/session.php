<?php

declare(strict_types=1);

$secure = (bool) config('session.secure', false);
$httpOnly = (bool) config('session.http_only', true);
$sameSite = (string) config('session.same_site', 'Lax');
$lifetimeMinutes = (int) config('session.lifetime', 120);

session_name((string) config('session.cookie', 'sigerd_session'));
session_set_cookie_params([
    'lifetime' => $lifetimeMinutes * 60,
    'path' => '/',
    'secure' => $secure,
    'httponly' => $httpOnly,
    'samesite' => $sameSite,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['_flash'])) {
    $_SESSION['_flash'] = [
        'current' => [],
        'next' => [],
        'old_current' => [],
        'old_next' => [],
    ];
}

$_SESSION['_flash']['current'] = $_SESSION['_flash']['next'] ?? [];
$_SESSION['_flash']['next'] = [];

$_SESSION['_flash']['old_current'] = $_SESSION['_flash']['old_next'] ?? [];
$_SESSION['_flash']['old_next'] = [];

$_SESSION['_csrf'] ??= [];
$_SESSION['_csrf_processed'] ??= [];

