<?php

declare(strict_types=1);

use App\Support\Flash;

$title = $title ?? 'Autenticacao';
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
<body class="auth-page">
<main class="auth-wrapper">
    <p class="muted center">
        <a href="<?= e(url('/')) ?>">Inicio</a> |
        <a href="<?= e(url('/planos')) ?>">Planos</a> |
        <a href="<?= e(url('/demonstracao')) ?>">Demonstracao</a>
    </p>
    <?php if (isset($flash['error'])): ?>
        <div class="alert alert-error"><?= e((string) $flash['error']) ?></div>
    <?php endif; ?>
    <?php if (isset($flash['warning'])): ?>
        <div class="alert alert-warning"><?= e((string) $flash['warning']) ?></div>
    <?php endif; ?>
    <?php if (isset($flash['success'])): ?>
        <div class="alert alert-success"><?= e((string) $flash['success']) ?></div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
</body>
</html>
