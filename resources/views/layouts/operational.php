<?php

declare(strict_types=1);

$title = $title ?? 'Area Operacional';
$auth = $_SESSION['auth'] ?? [];
$scopeList = is_array($auth['escopos'] ?? null) ? $auth['escopos'] : [];
$scopeLabel = $scopeList !== [] ? implode(', ', $scopeList) : 'PROPRIO_ORGAO';
$currentUri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$flash = App\Support\Flash::all();
$appVersion = (string) config('app.version', '1.0.0');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" type="image/png" sizes="256x256" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/shared/app.css')) ?>">
</head>
<body>
<header class="topbar">
    <div class="container container-wide topbar-inner">
        <div class="topbar-brand">
            <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="SIGERD" class="topbar-logo">
            <div>
                <strong>Operacional Institucional</strong>
                <div class="muted">Escopo: <?= e($scopeLabel) ?> | versao <?= e($appVersion) ?></div>
            </div>
        </div>

        <button
            class="menu-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="operational-main-nav"
            data-menu-toggle
            data-menu-target="operational-main-nav"
            aria-label="Abrir menu principal"
        >
            <span class="menu-toggle-lines" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <nav id="operational-main-nav" class="topbar-nav" data-nav-track>
            <span><?= e((string) ($auth['nome_completo'] ?? '')) ?></span>
            <a class="<?= str_starts_with($currentUri, '/operational/incidentes') ? 'is-active' : '' ?>" href="<?= e(url('/operational/incidentes')) ?>">Incidentes</a>
            <a class="<?= str_starts_with($currentUri, '/operational/plancon') ? 'is-active' : '' ?>" href="<?= e(url('/operational/plancon')) ?>">PLANCON</a>
            <a class="<?= str_starts_with($currentUri, '/operational/desastres') ? 'is-active' : '' ?>" href="<?= e(url('/operational/desastres')) ?>">Desastres</a>
            <a class="<?= str_starts_with($currentUri, '/operational/inteligencia') ? 'is-active' : '' ?>" href="<?= e(url('/operational/inteligencia')) ?>">Inteligencia</a>
            <a class="<?= str_starts_with($currentUri, '/operational/documentos') ? 'is-active' : '' ?>" href="<?= e(url('/operational/documentos')) ?>">Documentos</a>
            <a class="<?= str_starts_with($currentUri, '/operational/governanca') ? 'is-active' : '' ?>" href="<?= e(url('/operational/governanca')) ?>">Governanca</a>
            <a class="<?= str_starts_with($currentUri, '/operational/relatorios') ? 'is-active' : '' ?>" href="<?= e(url('/operational/relatorios/avancado')) ?>">Relatorios</a>
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
<footer class="system-footer">
    <div class="container container-wide system-footer-inner">
        <span>SIGERD operacional</span>
        <span>versao <?= e($appVersion) ?></span>
    </div>
</footer>
<button type="button" class="scroll-top-btn" data-scroll-top aria-label="Voltar ao topo">
    <span aria-hidden="true">&#8593;</span>
</button>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/uf-dynamic.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/municipio-autocomplete.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/ui-enhancements.js')) ?>" defer></script>
</body>
</html>
