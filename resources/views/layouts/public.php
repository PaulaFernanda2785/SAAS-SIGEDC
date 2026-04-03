<?php

declare(strict_types=1);

use App\Support\Flash;

$title = $title ?? config('app.name', 'SIGERD');
$flash = Flash::all();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/shared/app.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="container">
        <strong><?= e(config('app.name', 'SIGERD')) ?></strong>
        <nav>
            <a href="<?= e(url('/')) ?>">Inicio</a>
            <a href="<?= e(url('/planos')) ?>">Planos</a>
            <a href="<?= e(url('/demonstracao')) ?>">Demonstracao</a>
            <a href="<?= e(url('/login')) ?>">Login</a>
        </nav>
    </div>
</header>
<main class="container">
    <?php if (isset($flash['success'])): ?>
        <div class="alert alert-success"><?= e((string) $flash['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($flash['error'])): ?>
        <div class="alert alert-error"><?= e((string) $flash['error']) ?></div>
    <?php endif; ?>
    <?php if (isset($flash['warning'])): ?>
        <div class="alert alert-warning"><?= e((string) $flash['warning']) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>
</body>
</html>
