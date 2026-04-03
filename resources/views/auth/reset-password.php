<?php

declare(strict_types=1);

$token = $token ?? '';
?>
<section class="card auth-card">
    <h1>Redefinir senha</h1>
    <p>Use o token gerado para definir uma nova senha de acesso.</p>

    <form method="post" action="<?= e(url('/reset-password')) ?>" data-guard-submit="true">
        <?= App\Support\Csrf::field('auth_reset_password') ?>

        <div class="field">
            <label for="token">Token</label>
            <input id="token" name="token" type="text" value="<?= e((string) $token) ?>" required>
        </div>

        <div class="field">
            <label for="password">Nova senha</label>
            <input id="password" name="password" type="password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar nova senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <button type="submit" class="button" data-submit-label="Redefinir senha">
            <span class="button-text">Redefinir senha</span>
            <span class="button-loading" hidden>Processando...</span>
        </button>
    </form>

    <p class="muted">
        <a href="<?= e(url('/login')) ?>">Voltar ao login</a>
    </p>
</section>
