<?php

declare(strict_types=1);
?>
<section class="landing-hero" id="inicio">
    <div class="container landing-hero-inner reveal-on-scroll">
        <span class="landing-badge reveal-cascade-item" style="--reveal-delay: 0.08s;">Plataforma SaaS institucional</span>
        <h1 class="reveal-cascade-item" style="--reveal-delay: 0.22s;">SIGERD: clareza para decidir, coordenar e responder com segurança.</h1>
        <p class="reveal-cascade-item" style="--reveal-delay: 0.38s;">
            O SIGERD integra governança, operação e inteligência para órgãos públicos e equipes multiinstitucionais,
            com rastreabilidade completa desde a prevenção até a resposta ao desastre.
        </p>
        <div class="landing-actions reveal-cascade-item" style="--reveal-delay: 0.52s;">
            <a class="button" href="<?= e(url('/planos')) ?>">Escolher o melhor plano</a>
            <a class="button button-secondary" href="<?= e(url('/#funcionalidades')) ?>">Conhecer funcionalidades</a>
        </div>
        <div class="landing-metrics reveal-on-scroll" style="--reveal-delay: 0.64s;">
            <article class="reveal-cascade-item" style="--reveal-delay: 0.10s;">
                <strong>3 áreas integradas</strong>
                <span>Pública, administrativa SaaS e operacional</span>
            </article>
            <article class="reveal-cascade-item" style="--reveal-delay: 0.24s;">
                <strong>5 camadas de acesso</strong>
                <span>Autenticação, perfil, escopo, contrato e auditoria</span>
            </article>
            <article class="reveal-cascade-item" style="--reveal-delay: 0.38s;">
                <strong>Escopo por conta/órgão/unidade</strong>
                <span>Controle institucional com segregação real de dados</span>
            </article>
        </div>
    </div>
</section>

<section class="container landing-section" id="solucao">
    <header class="landing-section-header reveal-on-scroll">
        <span class="reveal-cascade-item" style="--reveal-delay: 0.08s;">Solução</span>
        <h2 class="reveal-cascade-item" style="--reveal-delay: 0.2s;">Um sistema único para risco, resposta e governança</h2>
        <p class="muted reveal-cascade-item" style="--reveal-delay: 0.32s;">
            Ao assinar o SIGERD, sua instituição passa a operar em um ecossistema completo: gestão SaaS, operação de incidentes,
            PLANCON modular, inteligência operacional, documentos, conformidade, controle territorial e expansão enterprise.
        </p>
    </header>
    <div class="landing-grid-3 reveal-on-scroll">
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.12s;">
            <h3>Gestão institucional SaaS</h3>
            <p>Centralize contas contratantes, órgãos operadores, unidades, usuários e perfis em uma base única e auditável.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.26s;">
            <h3>Planos, assinatura e faturamento</h3>
            <p>Controle catálogo de planos, ciclos mensal/anual, assinaturas, faturas e cobrança com trilha financeira segura.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.4s;">
            <h3>Operação de incidentes (SCI/SCO)</h3>
            <p>Abra incidentes e coordene briefing, comando, objetivos, períodos, registros e recursos mobilizados em tempo real.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.54s;">
            <h3>PLANCON modular</h3>
            <p>Estruture território, riscos, cenários, níveis de ativação, recursos e governança do plano com revisão e versionamento.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.68s;">
            <h3>Inteligência operacional</h3>
            <p>Consolide indicadores, mapa operacional, camadas geográficas e relatórios para apoiar decisão técnica e executiva.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.82s;">
            <h3>Documentos, anexos e auditoria</h3>
            <p>Vincule documentos por entidade, mantenha histórico de anexos e registre ações críticas para conformidade institucional.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 0.96s;">
            <h3>Escopo territorial por UF</h3>
            <p>Garanta segregação de dados por estado, órgão e unidade, com exceções controladas para perfis de governança global.</p>
        </article>
        <article class="landing-card reveal-cascade-item" style="--reveal-delay: 1.1s;">
            <h3>Recursos enterprise</h3>
            <p>Amplie a operação com API controlada, integrações externas, automações, analytics avançado e gestão de SLA/suporte.</p>
        </article>
    </div>
</section>

<section class="container landing-section" id="funcionalidades">
    <header class="landing-section-header reveal-on-scroll">
        <span class="reveal-cascade-item" style="--reveal-delay: 0.08s;">Funcionalidades centrais</span>
        <h2 class="reveal-cascade-item" style="--reveal-delay: 0.2s;">Objetividade operacional sem perder controle técnico</h2>
    </header>
    <div class="landing-grid-2 reveal-on-scroll">
        <article class="landing-feature reveal-cascade-item" style="--reveal-delay: 0.12s;">
            <h3>Trilha de auditoria e conformidade</h3>
            <p>Registro de ações críticas com escopo institucional, responsável, origem e resultado.</p>
        </article>
        <article class="landing-feature reveal-cascade-item" style="--reveal-delay: 0.24s;">
            <h3>Controle contratual e módulos</h3>
            <p>Liberação de capacidades por assinatura, com bloqueio seguro no backend.</p>
        </article>
        <article class="landing-feature reveal-cascade-item" style="--reveal-delay: 0.36s;">
            <h3>Escopo territorial por UF</h3>
            <p>Consulta e cadastro respeitando UF de origem e perfil, com exceção segura para ADMIN_MASTER.</p>
        </article>
        <article class="landing-feature reveal-cascade-item" style="--reveal-delay: 0.48s;">
            <h3>API e integrações enterprise</h3>
            <p>Consumo controlado por chave, escopos definidos e validação de módulo contratado.</p>
        </article>
    </div>
</section>

<section class="container landing-cta-strip reveal-on-scroll">
    <h2 class="reveal-cascade-item" style="--reveal-delay: 0.12s;">Pronto para evoluir a maturidade institucional da sua operação?</h2>
    <p class="reveal-cascade-item" style="--reveal-delay: 0.24s;">Compare os planos mensais e anuais e escolha a melhor combinação para iniciar sua operação com segurança e escalabilidade.</p>
    <a class="button reveal-cascade-item" href="<?= e(url('/planos')) ?>" style="--reveal-delay: 0.36s;">Escolher o melhor plano</a>
</section>
