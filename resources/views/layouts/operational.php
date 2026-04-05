<?php

declare(strict_types=1);

$title = $title ?? 'Area Operacional';
$auth = $_SESSION['auth'] ?? [];
$scopeList = is_array($auth['escopos'] ?? null) ? $auth['escopos'] : [];
$scopeLabel = $scopeList !== [] ? implode(', ', $scopeList) : 'PROPRIO_ORGAO';
$currentUri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
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
        <strong>Operacional Institucional</strong>
        <nav>
            <span><?= e((string) ($auth['nome_completo'] ?? '')) ?></span>
            <span class="muted">Escopo: <?= e($scopeLabel) ?></span>
            <a class="<?= str_starts_with($currentUri, '/operational/incidentes') ? 'is-active' : '' ?>" href="<?= e(url('/operational/incidentes')) ?>">Incidentes</a>
            <a class="<?= str_starts_with($currentUri, '/operational/plancon') ? 'is-active' : '' ?>" href="<?= e(url('/operational/plancon')) ?>">PLANCON</a>
            <a class="<?= str_starts_with($currentUri, '/operational/desastres') ? 'is-active' : '' ?>" href="<?= e(url('/operational/desastres')) ?>">Desastres</a>
            <a class="<?= str_starts_with($currentUri, '/operational/relatorios') ? 'is-active' : '' ?>" href="<?= e(url('/operational/relatorios/basico')) ?>">Relatorios</a>
            <a class="<?= $currentUri === '/operational' ? 'is-active' : '' ?>" href="<?= e(url('/operational')) ?>">Dashboard</a>
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
<script src="<?= e(url('/assets/js/shared/uf-dynamic.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/municipio-autocomplete.js')) ?>" defer></script>
</body>
</html>
