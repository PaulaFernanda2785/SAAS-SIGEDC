<?php

declare(strict_types=1);
?>
<section class="hero">
    <h1>Painel Operacional Institucional</h1>
    <p>Base pronta para entrada dos modulos de incidentes na fase operacional minima viavel.</p>
</section>

<section class="grid">
    <article class="card">
        <h2>Usuario autenticado</h2>
        <p><strong>Nome:</strong> <?= e((string) ($auth['nome_completo'] ?? '')) ?></p>
        <p><strong>Email:</strong> <?= e((string) ($auth['email_login'] ?? '')) ?></p>
        <p><strong>Perfil:</strong> <?= e((string) ($auth['perfil_primario'] ?? '')) ?></p>
        <p><strong>Assinatura:</strong> <?= e((string) ($auth['status_assinatura'] ?? 'N/A')) ?></p>
    </article>
    <article class="card">
        <h2>Separacao estrutural</h2>
        <ul>
            <li>Area operacional isolada da area administrativa.</li>
            <li>Controle por middleware de area.</li>
            <li>Trilha de acesso e auditoria registradas.</li>
            <li>Contexto contratual validado no login.</li>
        </ul>
    </article>
</section>
