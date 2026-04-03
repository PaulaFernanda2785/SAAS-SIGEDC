<?php

declare(strict_types=1);
?>
<section class="card auth-card">
    <h1>Recuperacao de senha</h1>
    <p>Informe seu login institucional para gerar um token de redefinicao.</p>

    <form method="post" action="<?= e(url('/forgot-password')) ?>" data-guard-submit="true">
        <?= App\Support\Csrf::field('auth_forgot_password') ?>
        <div class="field">
            <label for="email_login">Login</label>
            <input
                id="email_login"
                name="email_login"
                type="text"
                value="<?= e((string) App\Support\Flash::old('email_login', '')) ?>"
                required
            >
        </div>
        <button type="submit" class="button" data-submit-label="Gerar token">
            <span class="button-text">Gerar token</span>
            <span class="button-loading" hidden>Processando...</span>
        </button>
    </form>

    <p class="muted">
        Ja possui token?
        <a href="<?= e(url('/reset-password')) ?>">Redefinir senha</a>
    </p>
</section>
