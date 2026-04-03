<?php

declare(strict_types=1);

$errors = $errors ?? [];
?>
<section class="card auth-card">
    <h1>Acesso ao SIGERD</h1>
    <p>Use suas credenciais institucionais para iniciar a sessao.</p>

    <form method="post" action="<?= e(url('/login')) ?>" data-guard-submit="true">
        <?= App\Support\Csrf::field('auth_login') ?>
        <div class="field">
            <label for="email_login">Login</label>
            <input id="email_login" name="email_login" type="text" value="<?= e((string) App\Support\Flash::old('email_login', '')) ?>" required>
            <?php if (isset($errors['email_login'])): ?>
                <small class="error"><?= e((string) $errors['email_login']) ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required>
            <?php if (isset($errors['password'])): ?>
                <small class="error"><?= e((string) $errors['password']) ?></small>
            <?php endif; ?>
        </div>

        <button type="submit" class="button" data-submit-label="Entrar">
            <span class="button-text">Entrar</span>
            <span class="button-loading" hidden>Processando...</span>
        </button>
    </form>
</section>

