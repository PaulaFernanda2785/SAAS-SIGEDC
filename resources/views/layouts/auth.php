<?php

declare(strict_types=1);

use App\Support\Flash;

$title = $title ?? 'Autenticacao';
$flash = Flash::all();
$appVersion = (string) config('app.version', '1.0.0');

$uriPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$basePath = app_base_path();
if (
    $basePath !== ''
    && ($uriPath === $basePath || str_starts_with($uriPath, $basePath . '/'))
) {
    $uriPath = substr($uriPath, strlen($basePath));
    $uriPath = $uriPath === '' ? '/' : $uriPath;
}

$isHome = $uriPath === '/';
$isPlans = $uriPath === '/planos';
$isLogin = in_array($uriPath, ['/login', '/acessar-plataforma', '/forgot-password', '/reset-password'], true);
$isLoginAccessPage = in_array($uriPath, ['/login', '/acessar-plataforma'], true);
$authAccessNavClass = trim('public-nav-access ' . ($isLogin ? 'is-active' : ''));
$authWrapperClass = 'container auth-wrapper' . ($isLoginAccessPage ? ' auth-wrapper--login' : '');
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
<body class="public-body auth-shell">
<header class="public-header">
    <div class="container container-wide public-header-inner">
        <div class="public-brand-zone">
            <a class="public-brand" href="<?= e(url('/')) ?>" aria-label="Sistema Integrado de Gerenciamento de Riscos e Desastres - Inicio">
                <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="Logo do sistema" class="public-brand-logo">
                <span class="public-brand-text">
                    <strong>Sistema Integrado de Gerenciamento de Riscos e Desastres</strong>
                </span>
            </a>

            <button
                class="menu-toggle public-menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="auth-main-nav"
                data-menu-toggle
                data-menu-target="auth-main-nav"
                aria-label="Abrir menu principal"
            >
                <span class="menu-toggle-lines" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>

        <nav id="auth-main-nav" class="public-nav" aria-label="Menu principal" data-nav-track>
            <a class="<?= $isHome ? 'is-active' : '' ?>" href="<?= e(url('/')) ?>">Inicio</a>
            <a href="<?= e(url('/#solucao')) ?>">Solucao</a>
            <a href="<?= e(url('/#funcionalidades')) ?>">Funcionalidades</a>
            <a class="<?= $isPlans ? 'is-active' : '' ?>" href="<?= e(url('/planos')) ?>">Planos</a>
            <a class="<?= e($authAccessNavClass) ?>" href="<?= e(url('/acessar-plataforma')) ?>">Acessar plataforma</a>
        </nav>

        <div class="public-head-actions">
            <span class="app-version">versao <?= e($appVersion) ?></span>
            <a class="public-cta" href="<?= e(url('/acessar-plataforma')) ?>">Acessar plataforma</a>
        </div>
    </div>
</header>

<main class="public-main auth-main-shell">
    <section class="<?= e($authWrapperClass) ?>">
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
    </section>
</main>

<footer class="public-footer">
    <div class="container container-wide public-footer-inner">
        <div class="public-footer-brand">
            <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="Logo do sistema" class="public-footer-logo">
            <div class="public-footer-copy">
                <strong>Sistema Integrado de Gerenciamento de Riscos e Desastres</strong>
                <p>Ambiente seguro de autenticacao institucional.</p>
            </div>
        </div>

        <div class="public-footer-links">
            <a href="<?= e(url('/')) ?>">Inicio</a>
            <a href="<?= e(url('/planos')) ?>">Planos</a>
            <a href="<?= e(url('/acessar-plataforma')) ?>">Acessar plataforma</a>
        </div>

        <div class="public-footer-meta">
            <span>SaaS institucional</span>
            <span>versao <?= e($appVersion) ?></span>
            <span><?= e(date('Y')) ?>. Todos os direitos reservados.</span>
        </div>
    </div>
</footer>

<button type="button" class="scroll-top-btn" data-scroll-top aria-label="Voltar ao topo">
    <span aria-hidden="true">&#8593;</span>
</button>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/ui-enhancements.js')) ?>" defer></script>
</body>
</html>
