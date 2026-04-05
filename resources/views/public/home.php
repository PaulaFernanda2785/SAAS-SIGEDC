<?php

declare(strict_types=1);
?>
<section class="landing-hero" id="inicio">
    <div class="container landing-hero-inner reveal-on-scroll">
        <span class="landing-badge reveal-cascade-item" style="--reveal-delay: 0.08s;">Plataforma SaaS institucional</span>
        <h1 class="reveal-cascade-item" style="--reveal-delay: 0.22s;">SIGERD: clareza para decidir, coordenar e responder com seguranca.</h1>
        <p class="reveal-cascade-item" style="--reveal-delay: 0.38s;">
            O SIGERD integra governanca, operacao e inteligencia para orgaos publicos e equipes multiinstitucionais,
            com rastreabilidade completa desde a prevencao ate a resposta ao desastre.
        </p>
        <div class="landing-actions reveal-cascade-item" style="--reveal-delay: 0.52s;">
            <a class="button" href="<?= e(url('/demonstracao')) ?>">Solicitar demonstracao</a>
            <a class="button button-secondary" href="<?= e(url('/planos')) ?>">Conhecer planos</a>
        </div>
        <div class="landing-metrics reveal-on-scroll" style="--reveal-delay: 0.64s;">
            <article class="reveal-cascade-item" style="--reveal-delay: 0.10s;">
                <strong>3 areas integradas</strong>
                <span>Publica, administrativa SaaS e operacional</span>
            </article>
            <article class="reveal-cascade-item" style="--reveal-delay: 0.24s;">
                <strong>5 camadas de acesso</strong>
                <span>Autenticacao, perfil, escopo, contrato e auditoria</span>
            </article>
            <article class="reveal-cascade-item" style="--reveal-delay: 0.38s;">
                <strong>Escopo por conta/orgao/unidade</strong>
                <span>Controle institucional com segregacao real de dados</span>
            </article>
        </div>
    </div>
</section>

<section class="container landing-section" id="solucao">
    <header class="landing-section-header reveal-on-scroll">
        <span class="reveal-cascade-item" style="--reveal-delay: 0.08s;">Solucao</span>
        <h2 class="reveal-cascade-item" style="--reveal-delay: 0.2s;">Um sistema unico para risco, resposta e governanca</h2>
    </header>
    <div class="landing-grid-3 reveal-on-scroll">
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.12s;">
            <h3>Gestao institucional SaaS</h3>
            <p>Administre contas, orgaos, unidades, usuarios, perfis, assinaturas e modulos com padrao de seguranca empresarial.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.26s;">
            <h3>Operacao de incidentes</h3>
            <p>Abra incidentes, mantenha briefing, comando, periodos e registros em fluxo continuo para resposta coordenada.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.4s;">
            <h3>PLANCON e inteligencia</h3>
            <p>Estruture planos, riscos, cenarios e dados analiticos para apoiar decisoes criticas em tempo oportuno.</p>
        </article>
    </div>
</section>

<section class="container landing-section" id="funcionalidades">
    <header class="landing-section-header">
        <span>Funcionalidades centrais</span>
        <h2>Objetividade operacional sem perder controle tecnico</h2>
    </header>
    <div class="landing-grid-2">
        <article class="landing-feature">
            <h3>Trilha de auditoria e conformidade</h3>
            <p>Registro de acoes criticas com escopo institucional, responsavel, origem e resultado.</p>
        </article>
        <article class="landing-feature">
            <h3>Controle contratual e modulos</h3>
            <p>Liberacao de capacidades por assinatura, com bloqueio seguro no backend.</p>
        </article>
        <article class="landing-feature">
            <h3>Escopo territorial por UF</h3>
            <p>Consulta e cadastro respeitando UF de origem e perfil, com excecao segura para ADMIN_MASTER.</p>
        </article>
        <article class="landing-feature">
            <h3>API e integracoes enterprise</h3>
            <p>Consumo controlado por chave, escopos definidos e validacao de modulo contratado.</p>
        </article>
    </div>
</section>

<section class="container landing-cta-strip">
    <h2>Pronto para evoluir a maturidade institucional da sua operacao?</h2>
    <p>Agende uma demonstracao e valide o SIGERD com seu cenario real de governanca e resposta.</p>
    <a class="button" href="<?= e(url('/demonstracao')) ?>">Agendar demonstracao</a>
</section>
