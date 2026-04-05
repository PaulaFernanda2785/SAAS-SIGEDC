<?php

declare(strict_types=1);
?>
<section class="hero">
    <h1>Painel Administrativo SaaS</h1>
    <p>Centro de comando da Fase 1 para gestao institucional, comercial e status contratual.</p>
</section>

<section class="grid">
    <article class="card">
        <h2>Usuario autenticado</h2>
        <p><strong>Nome:</strong> <?= e((string) ($auth['nome_completo'] ?? '')) ?></p>
        <p><strong>Email:</strong> <?= e((string) ($auth['email_login'] ?? '')) ?></p>
        <p><strong>Perfil:</strong> <?= e((string) ($auth['perfil_primario'] ?? '')) ?></p>
        <p><strong>UF de contexto:</strong> <?= e((string) ($auth['uf_sigla'] ?? 'N/A')) ?></p>
        <p><strong>Status assinatura:</strong> <?= e((string) ($auth['status_assinatura'] ?? 'N/A')) ?></p>
    </article>
    <article class="card">
        <h2>Resumo Fase 1</h2>
        <p><strong>Contas:</strong> <?= e((string) ($summary['contas'] ?? 0)) ?></p>
        <p><strong>Orgaos:</strong> <?= e((string) ($summary['orgaos'] ?? 0)) ?></p>
        <p><strong>Unidades:</strong> <?= e((string) ($summary['unidades'] ?? 0)) ?></p>
        <p><strong>Usuarios:</strong> <?= e((string) ($summary['usuarios'] ?? 0)) ?></p>
        <p><strong>Perfis:</strong> <?= e((string) ($summary['perfis'] ?? 0)) ?></p>
        <p><strong>Planos:</strong> <?= e((string) ($summary['planos'] ?? 0)) ?></p>
        <p><strong>Assinaturas ativas:</strong> <?= e((string) ($summary['assinaturas_ativas'] ?? 0)) ?></p>
    </article>
    <article class="card">
        <h2>Atalhos operacionais</h2>
        <ul>
            <li><a href="<?= e(url('/admin/institucional')) ?>">Cadastrar contas, orgaos, unidades e usuarios</a></li>
            <li><a href="<?= e(url('/admin/comercial')) ?>">Gerenciar planos, assinaturas e modulos</a></li>
            <li><a href="<?= e(url('/planos')) ?>">Validar pagina publica de planos</a></li>
        </ul>
    </article>
</section>
