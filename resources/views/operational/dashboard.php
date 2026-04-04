<?php

declare(strict_types=1);

$summary = $summary ?? [];
$recentIncidents = $recentIncidents ?? [];
$scope = $scope ?? [];
?>
<section class="hero">
    <h1>Painel Operacional Institucional</h1>
    <p>Visao consolidada do nucleo operacional minimo viavel da Fase 2.</p>
</section>

<section class="grid">
    <article class="card kpi-card">
        <h2>Incidentes abertos</h2>
        <p class="kpi-value"><?= e((string) ($summary['incidentes_abertos'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Em andamento</h2>
        <p class="kpi-value"><?= e((string) ($summary['incidentes_em_andamento'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Controlados</h2>
        <p class="kpi-value"><?= e((string) ($summary['incidentes_controlados'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Encerrados</h2>
        <p class="kpi-value"><?= e((string) ($summary['incidentes_encerrados'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Periodos ativos</h2>
        <p class="kpi-value"><?= e((string) ($summary['periodos_ativos'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Registros (24h)</h2>
        <p class="kpi-value"><?= e((string) ($summary['registros_24h'] ?? 0)) ?></p>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card">
        <h2>Contexto de acesso</h2>
        <ul>
            <li><strong>Usuario:</strong> <?= e((string) ($auth['nome_completo'] ?? '')) ?></li>
            <li><strong>Perfil primario:</strong> <?= e((string) ($auth['perfil_primario'] ?? '')) ?></li>
            <li><strong>Escopo ativo:</strong> <?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></li>
            <li><strong>Assinatura:</strong> <?= e((string) ($auth['status_assinatura'] ?? 'N/A')) ?></li>
        </ul>
        <div class="actions">
            <a class="button" href="<?= e(url('/operational/incidentes')) ?>">Operar incidentes</a>
            <a class="button button-secondary" href="<?= e(url('/operational/relatorios/basico')) ?>">Ver relatorios</a>
        </div>
    </article>

    <article class="card table-card">
        <h2>Ultimos incidentes no escopo</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Numero</th>
                    <th>Incidente</th>
                    <th>Status</th>
                    <th>Abertura</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentIncidents as $row): ?>
                    <tr>
                        <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                        <td><?= e((string) $row['nome_incidente']) ?></td>
                        <td><span class="tag"><?= e((string) $row['status_incidente']) ?></span></td>
                        <td><?= e((string) $row['data_hora_abertura']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentIncidents === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Nenhum incidente disponivel no escopo atual.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
