<?php

declare(strict_types=1);

$title = $title ?? 'Area Operacional';
$auth = $_SESSION['auth'] ?? [];
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
            <a href="<?= e(url('/operational')) ?>">Dashboard</a>
            <form method="post" action="<?= e(url('/logout')) ?>" class="inline-form">
                <?= App\Support\Csrf::field('auth_logout') ?>
                <button type="submit">Sair</button>
            </form>
        </nav>
    </div>
</header>
<main class="container">
    <?= $content ?? '' ?>
</main>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
</body>
</html>

