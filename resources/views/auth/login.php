<?php

declare(strict_types=1);

$errors = $errors ?? [];
?>
<section class="auth-login-shell reveal-on-scroll">
    <article class="auth-login-intro reveal-cascade-item" style="--reveal-delay: 0.08s;">
        <span class="landing-badge">Acesso institucional seguro</span>
        <h1>Entre na plataforma SIGERD e mantenha sua operacao sob controle.</h1>
        <p>
            Area dedicada para equipes institucionais com rastreabilidade, escopo por conta/orgao/unidade
            e trilha de auditoria para decisoes criticas.
        </p>
        <ul class="auth-login-highlights">
            <li>Controle por perfis e camadas de acesso</li>
            <li>Operacao de incidentes e PLANCON no mesmo ecossistema</li>
            <li>Historico operacional, relatorios e governanca</li>
        </ul>
        <div class="auth-login-actions">
            <a class="button button-secondary" href="<?= e(url('/planos')) ?>">Ver planos</a>
            <a class="button" href="<?= e(url('/demonstracao?plano=PRO&ciclo=MENSAL')) ?>">Iniciar avaliacao</a>
        </div>
    </article>

    <article class="card auth-card auth-login-card reveal-cascade-item" style="--reveal-delay: 0.2s;">
        <h2>Acessar plataforma</h2>
        <p class="muted">Use seu login institucional para continuar.</p>

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

            <button type="submit" class="button auth-login-submit" data-submit-label="Entrar na plataforma">
                <span class="button-text">Entrar na plataforma</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>

        <p class="muted auth-login-help">
            Esqueceu a senha?
            <a href="<?= e(url('/forgot-password')) ?>">Recuperar acesso</a>
        </p>
    </article>
</section>
