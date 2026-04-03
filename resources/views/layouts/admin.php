<?php

declare(strict_types=1);

$title = $title ?? 'Area Administrativa';
$auth = $_SESSION['auth'] ?? [];
$flash = App\Support\Flash::all();
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
        <strong>Admin SaaS</strong>
        <nav>
            <span><?= e((string) ($auth['nome_completo'] ?? '')) ?></span>
            <a href="<?= e(url('/admin')) ?>">Dashboard</a>
            <a href="<?= e(url('/admin/institucional')) ?>">Institucional</a>
            <a href="<?= e(url('/admin/comercial')) ?>">Comercial</a>
            <form method="post" action="<?= e(url('/logout')) ?>" class="inline-form">
                <?= App\Support\Csrf::field('auth_logout') ?>
                <button type="submit">Sair</button>
            </form>
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
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
</body>
</html>
