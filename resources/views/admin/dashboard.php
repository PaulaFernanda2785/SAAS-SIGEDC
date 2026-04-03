<?php

declare(strict_types=1);
?>
<section class="hero">
    <h1>Painel Administrativo SaaS</h1>
    <p>Fase 0 estabilizada para evolucao da identidade institucional e comercial na Fase 1.</p>
</section>

<section class="grid">
    <article class="card">
        <h2>Usuario autenticado</h2>
        <p><strong>Nome:</strong> <?= e((string) ($auth['nome_completo'] ?? '')) ?></p>
        <p><strong>Email:</strong> <?= e((string) ($auth['email_login'] ?? '')) ?></p>
        <p><strong>Perfil:</strong> <?= e((string) ($auth['perfil_primario'] ?? '')) ?></p>
    </article>
    <article class="card">
        <h2>Checklist Fase 0</h2>
        <ul>
            <li>Bootstrap inicial carregado.</li>
            <li>Sessao persistida e rastreavel.</li>
            <li>Auditoria minima ativa.</li>
            <li>Separacao de areas aplicada.</li>
        </ul>
    </article>
</section>

