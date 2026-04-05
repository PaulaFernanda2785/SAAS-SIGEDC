<?php

declare(strict_types=1);

$scope = $scope ?? [];
$filters = $filters ?? [];
$statusDistribution = $statusDistribution ?? [];
$hotspots = $hotspots ?? [];
$mapPoints = $mapPoints ?? [];
$responseKpi = $responseKpi ?? [];
$planconCoverage = $planconCoverage ?? [];
$trendByDay = $trendByDay ?? [];
?>
<section class="hero">
    <h1>Inteligencia Operacional</h1>
    <p>Painel analitico por escopo institucional com hotspots, tendencias e pontos de mapa.</p>
</section>

<section class="card">
    <h2>Filtros</h2>
    <form method="get" action="<?= e(url('/operational/inteligencia')) ?>" class="grid grid-2">
        <div class="field">
            <label for="intel_data_inicio">Data inicio</label>
            <input id="intel_data_inicio" name="data_inicio" type="date" value="<?= e((string) ($filters['data_inicio'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="intel_data_fim">Data fim</label>
            <input id="intel_data_fim" name="data_fim" type="date" value="<?= e((string) ($filters['data_fim'] ?? '')) ?>">
        </div>
        <div class="actions">
            <button type="submit">Aplicar filtros</button>
            <a class="button button-secondary" href="<?= e(url('/operational/inteligencia')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="grid mt-1">
    <article class="card kpi-card">
        <h2>Escopo ativo</h2>
        <p class="kpi-value"><?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Total incidentes</h2>
        <p class="kpi-value"><?= e((string) ($responseKpi['total_incidentes'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Media briefing (min)</h2>
        <p class="kpi-value"><?= e((string) (isset($responseKpi['media_minutos_primeiro_briefing']) ? number_format((float) $responseKpi['media_minutos_primeiro_briefing'], 1, ',', '.') : '0')) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>PLANCON ativos</h2>
        <p class="kpi-value"><?= e((string) ($planconCoverage['total_plancons_ativos'] ?? 0)) ?></p>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Status de incidentes</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($statusDistribution as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['status_incidente'] ?? 'N/A')) ?></td>
                        <td><?= e((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($statusDistribution === []): ?>
                    <tr>
                        <td colspan="2" class="muted">Sem dados para os filtros informados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Hotspots por municipio</h2>
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
                        <td><?= e((string) ($row['municipio'] ?? 'N/A')) ?></td>
                        <td><?= e((string) ($row['total_incidentes'] ?? 0)) ?></td>
                        <td><?= e((string) ($row['incidentes_ativos'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($hotspots === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Sem hotspots para o periodo filtrado.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Pontos para mapa operacional</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Municipio</th>
                <th>UF</th>
                <th>Incidentes</th>
                <th>Latitude</th>
                <th>Longitude</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($mapPoints as $row): ?>
                <tr>
                    <td><?= e((string) ($row['municipio_nome'] ?? 'N/A')) ?></td>
                    <td><?= e((string) ($row['uf_sigla_ref'] ?? '')) ?></td>
                    <td><?= e((string) ($row['total_incidentes'] ?? 0)) ?></td>
                    <td><?= e((string) ($row['latitude'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['longitude'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($mapPoints === []): ?>
                <tr>
                    <td colspan="5" class="muted">Sem pontos de mapa para o escopo/periodo atual.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card mt-1">
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
            <?php foreach ($trendByDay as $row): ?>
                <tr>
                    <td><?= e((string) ($row['referencia_data'] ?? '')) ?></td>
                    <td><?= e((string) ($row['total_incidentes'] ?? 0)) ?></td>
                    <td><?= e((string) ($row['incidentes_ativos'] ?? 0)) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($trendByDay === []): ?>
                <tr>
                    <td colspan="3" class="muted">Sem dados de tendencia para o periodo informado.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
