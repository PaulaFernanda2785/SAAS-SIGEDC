<?php

declare(strict_types=1);

$title = $title ?? 'Area Administrativa';
$auth = $_SESSION['auth'] ?? [];
$flash = App\Support\Flash::all();
$appVersion = (string) config('app.version', '1.0.0');
$currentUri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
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
                <strong>Admin SaaS</strong>
                <div class="muted">UF: <?= e((string) ($auth['uf_sigla'] ?? 'N/A')) ?> | versao <?= e($appVersion) ?></div>
            </div>
        </div>

        <button
            class="menu-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="admin-main-nav"
            data-menu-toggle
            data-menu-target="admin-main-nav"
            aria-label="Abrir menu principal"
        >
            <span class="menu-toggle-lines" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <nav id="admin-main-nav" class="topbar-nav" data-nav-track>
            <span><?= e((string) ($auth['nome_completo'] ?? '')) ?></span>
            <a class="<?= $currentUri === '/admin' ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>">Dashboard</a>
            <a class="<?= str_starts_with($currentUri, '/admin/institucional') ? 'is-active' : '' ?>" href="<?= e(url('/admin/institucional')) ?>">Institucional</a>
            <a class="<?= str_starts_with($currentUri, '/admin/comercial') ? 'is-active' : '' ?>" href="<?= e(url('/admin/comercial')) ?>">Comercial</a>
            <a class="<?= str_starts_with($currentUri, '/admin/enterprise') ? 'is-active' : '' ?>" href="<?= e(url('/admin/enterprise')) ?>">Enterprise</a>
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
        <span>SIGERD administrativo</span>
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
