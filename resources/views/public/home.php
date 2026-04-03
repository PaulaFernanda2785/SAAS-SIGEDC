<?php

declare(strict_types=1);
?>
<section class="hero">
    <h1>SIGERD - Nucleo SaaS e identidade institucional</h1>
    <p>Plataforma para gestao institucional de riscos e desastres com controle de contas, orgaos, usuarios e assinaturas.</p>
    <div class="actions">
        <a class="button" href="<?= e(url('/demonstracao')) ?>">Solicitar demonstracao</a>
        <a class="button button-secondary" href="<?= e(url('/planos')) ?>">Ver planos</a>
    </div>
</section>

<section class="grid">
    <article class="card">
        <h2>Identidade institucional</h2>
        <p>Cadastre conta contratante, orgaos, unidades e perfis com separacao de contexto desde o inicio.</p>
    </article>
    <article class="card">
        <h2>Nucleo SaaS</h2>
        <p>Gestao de planos, assinaturas e liberacao de modulos com bloqueio contratual basico no acesso.</p>
    </article>
    <article class="card">
        <h2>Seguranca e rastreabilidade</h2>
        <p>Login com trilha de acesso, auditoria funcional e recuperacao de senha por token.</p>
    </article>
</section>
