<?php

declare(strict_types=1);

$filters = $filters ?? [];
$statusSummary = $statusSummary ?? [];
$recordsByType = $recordsByType ?? [];
$incidents = $incidents ?? [];
$recentRecords = $recentRecords ?? [];
$incidentOptions = $incidentOptions ?? [];
$scope = $scope ?? [];

$totalIncidentesFiltrados = count($incidents);
$totalRegistrosFiltrados = count($recentRecords);
?>
<section class="hero">
    <h1>Relatorio Operacional Basico</h1>
    <p>Consolidado de incidentes e diario operacional por escopo institucional.</p>
</section>

<section class="grid">
    <article class="card kpi-card">
        <h2>Incidentes filtrados</h2>
        <p class="kpi-value"><?= e((string) $totalIncidentesFiltrados) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Registros filtrados</h2>
        <p class="kpi-value"><?= e((string) $totalRegistrosFiltrados) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Escopo ativo</h2>
        <p class="kpi-value"><?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>
    </article>
</section>

<section class="card mt-1">
    <h2>Filtros</h2>
    <form method="get" action="<?= e(url('/operational/relatorios/basico')) ?>" class="grid grid-2">
        <div class="field">
            <label for="f_incidente_id">Incidente</label>
            <select id="f_incidente_id" name="incidente_id">
                <option value="">Todos</option>
                <?php foreach ($incidentOptions as $item): ?>
                    <option value="<?= e((string) $item['id']) ?>" <?= ((int) ($filters['incidente_id'] ?? 0) === (int) $item['id']) ? 'selected' : '' ?>>
                        <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="f_status_incidente">Status do incidente</label>
            <select id="f_status_incidente" name="status_incidente">
                <option value="">Todos</option>
                <?php foreach (['ABERTO', 'EM_ANDAMENTO', 'CONTROLADO', 'ENCERRADO'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= (($filters['status_incidente'] ?? null) === $status) ? 'selected' : '' ?>>
                        <?= e($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="f_data_inicio">Data inicio</label>
            <input id="f_data_inicio" name="data_inicio" type="date" value="<?= e((string) ($filters['data_inicio'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="f_data_fim">Data fim</label>
            <input id="f_data_fim" name="data_fim" type="date" value="<?= e((string) ($filters['data_fim'] ?? '')) ?>">
        </div>
        <div class="actions">
            <button type="submit">Aplicar filtros</button>
            <a class="button button-secondary" href="<?= e(url('/operational/relatorios/basico')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Incidentes por status</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($statusSummary as $row): ?>
                    <tr>
                        <td><?= e((string) $row['status_incidente']) ?></td>
                        <td><?= e((string) $row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($statusSummary === []): ?>
                    <tr>
                        <td colspan="2" class="muted">Sem dados para os filtros informados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Registros por tipo</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recordsByType as $row): ?>
                    <tr>
                        <td><?= e((string) $row['tipo_registro']) ?></td>
                        <td><?= e((string) $row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recordsByType === []): ?>
                    <tr>
                        <td colspan="2" class="muted">Sem registros para os filtros informados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Incidentes no periodo</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Numero</th>
                <th>Incidente</th>
                <th>Status</th>
                <th>Tipo</th>
                <th>Registros</th>
                <th>Ultimo periodo</th>
                <th>Abertura</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $row): ?>
                <tr>
                    <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                    <td><?= e((string) $row['nome_incidente']) ?></td>
                    <td><?= e((string) $row['status_incidente']) ?></td>
                    <td><?= e((string) $row['tipo_ocorrencia']) ?></td>
                    <td><?= e((string) $row['total_registros']) ?></td>
                    <td><?= e((string) ($row['ultimo_periodo'] ?? '-')) ?></td>
                    <td><?= e((string) $row['data_hora_abertura']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($incidents === []): ?>
                <tr>
                    <td colspan="7" class="muted">Nenhum incidente encontrado para os filtros aplicados.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card mt-1">
    <h2>Registros operacionais recentes</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data/hora</th>
                <th>Incidente</th>
                <th>Tipo</th>
                <th>Titulo</th>
                <th>Status</th>
                <th>Criticidade</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentRecords as $row): ?>
                <tr>
                    <td><?= e((string) $row['data_hora_registro']) ?></td>
                    <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                    <td><?= e((string) $row['tipo_registro']) ?></td>
                    <td><?= e((string) $row['titulo_registro']) ?></td>
                    <td><?= e((string) $row['status_registro']) ?></td>
                    <td><?= e((string) $row['criticidade']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentRecords === []): ?>
                <tr>
                    <td colspan="6" class="muted">Nenhum registro operacional encontrado.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
