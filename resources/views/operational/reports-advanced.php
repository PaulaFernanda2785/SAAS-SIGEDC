<?php

declare(strict_types=1);

$scope = $scope ?? [];
$filters = $filters ?? [];
$trend = $trend ?? [];
$hotspots = $hotspots ?? [];
$auditFrequency = $auditFrequency ?? [];
$documentsByEntity = $documentsByEntity ?? [];
$activeAlerts = $activeAlerts ?? [];
$recentExecutions = $recentExecutions ?? [];
?>
<section class="hero">
    <h1>Relatorio Operacional Avancado</h1>
    <p>Visao consolidada de tendencias, hotspots, conformidade e rastreabilidade documental por escopo.</p>
</section>

<section class="card">
    <h2>Filtros</h2>
    <form method="get" action="<?= e(url('/operational/relatorios/avancado')) ?>" class="grid grid-2">
        <div class="field">
            <label for="adv_data_inicio">Data inicio</label>
            <input id="adv_data_inicio" name="data_inicio" type="date" value="<?= e((string) ($filters['data_inicio'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="adv_data_fim">Data fim</label>
            <input id="adv_data_fim" name="data_fim" type="date" value="<?= e((string) ($filters['data_fim'] ?? '')) ?>">
        </div>
        <div class="actions">
            <button type="submit">Aplicar filtros</button>
            <a class="button button-secondary" href="<?= e(url('/operational/relatorios/avancado')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="grid mt-1">
    <article class="card kpi-card">
        <h2>Escopo ativo</h2>
        <p class="kpi-value"><?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Pontos de tendencia</h2>
        <p class="kpi-value"><?= e((string) count($trend)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Municipios hotspot</h2>
        <p class="kpi-value"><?= e((string) count($hotspots)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Acoes auditadas</h2>
        <p class="kpi-value"><?= e((string) count($auditFrequency)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Alertas ativos</h2>
        <p class="kpi-value"><?= e((string) count($activeAlerts)) ?></p>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Tendencia diaria</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Total incidentes</th>
                    <th>Incidentes ativos</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($trend as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['referencia_data'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['total_incidentes'] ?? 0)) ?></td>
                        <td><?= e((string) ($row['incidentes_ativos'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($trend === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Sem dados de tendencia para o periodo selecionado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Hotspots</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Municipio</th>
                    <th>Total</th>
                    <th>Ativos</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($hotspots as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['municipio'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['total_incidentes'] ?? 0)) ?></td>
                        <td><?= e((string) ($row['incidentes_ativos'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($hotspots === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Sem hotspots para os filtros informados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Frequencia de acoes auditadas</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Modulo</th>
                    <th>Acao</th>
                    <th>Resultado</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($auditFrequency as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['modulo_codigo'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['acao'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['resultado'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($auditFrequency === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem frequencia de acoes para o periodo informado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Documentos por entidade</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Entidade</th>
                    <th>Total anexos</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($documentsByEntity as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['entidade_tipo'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($documentsByEntity === []): ?>
                    <tr>
                        <td colspan="2" class="muted">Sem anexos registrados no escopo atual.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Alertas operacionais ativos</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Nivel</th>
                <th>Codigo</th>
                <th>Mensagem</th>
                <th>Incidente</th>
                <th>Gerado em</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($activeAlerts as $alert): ?>
                <tr>
                    <td><?= e((string) ($alert['nivel_alerta'] ?? '-')) ?></td>
                    <td><?= e((string) ($alert['alerta_codigo'] ?? '-')) ?></td>
                    <td><?= e((string) ($alert['mensagem_alerta'] ?? '-')) ?></td>
                    <td><?= e((string) ($alert['numero_ocorrencia'] ?? '-')) ?></td>
                    <td><?= e((string) ($alert['gerado_em'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($activeAlerts === []): ?>
                <tr>
                    <td colspan="5" class="muted">Sem alertas operacionais ativos no periodo/escopo informado.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card mt-1">
    <h2>Execucoes recentes de relatorios avancados</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Total registros</th>
                <th>Gerado em</th>
                <th>Usuario</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentExecutions as $row): ?>
                <tr>
                    <td><?= e((string) ($row['id'] ?? '')) ?></td>
                    <td><?= e((string) ($row['tipo_relatorio'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['status_execucao'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['total_registros'] ?? 0)) ?></td>
                    <td><?= e((string) ($row['gerado_em'] ?? '')) ?></td>
                    <td><?= e((string) ($row['usuario_nome'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentExecutions === []): ?>
                <tr>
                    <td colspan="6" class="muted">Nenhuma execucao encontrada.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

