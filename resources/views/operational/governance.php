<?php

declare(strict_types=1);

$scope = $scope ?? [];
$filters = $filters ?? [];
$summary = $summary ?? [];
$actionFrequency = $actionFrequency ?? [];
$recentLogs = $recentLogs ?? [];
$recentTermAcceptances = $recentTermAcceptances ?? [];
$term = $term ?? [];
$termAccepted = (bool) ($termAccepted ?? false);
?>
<section class="hero">
    <h1>Governanca Operacional</h1>
    <p>Painel de conformidade com trilha de auditoria, frequencia de acoes criticas e aceite de termo vigente.</p>
</section>

<section class="grid">
    <article class="card kpi-card">
        <h2>Eventos auditados</h2>
        <p class="kpi-value"><?= e((string) ($summary['total_eventos'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Sucesso</h2>
        <p class="kpi-value"><?= e((string) ($summary['total_sucesso'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Falhas</h2>
        <p class="kpi-value"><?= e((string) ($summary['total_falha'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Acessos negados</h2>
        <p class="kpi-value"><?= e((string) ($summary['total_negado'] ?? 0)) ?></p>
    </article>
</section>

<section class="card mt-1">
    <h2>Filtros de auditoria</h2>
    <form method="get" action="<?= e(url('/operational/governanca')) ?>" class="grid grid-2">
        <div class="field">
            <label for="gov_data_inicio">Data inicio</label>
            <input id="gov_data_inicio" name="data_inicio" type="date" value="<?= e((string) ($filters['data_inicio'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="gov_data_fim">Data fim</label>
            <input id="gov_data_fim" name="data_fim" type="date" value="<?= e((string) ($filters['data_fim'] ?? '')) ?>">
        </div>
        <div class="field">
            <label for="gov_resultado">Resultado</label>
            <select id="gov_resultado" name="resultado">
                <option value="">Todos</option>
                <?php foreach (['SUCESSO', 'FALHA', 'NEGADO'] as $result): ?>
                    <option value="<?= e($result) ?>" <?= (($filters['resultado'] ?? null) === $result) ? 'selected' : '' ?>>
                        <?= e($result) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="gov_modulo_codigo">Modulo</label>
            <input id="gov_modulo_codigo" name="modulo_codigo" type="text" value="<?= e((string) ($filters['modulo_codigo'] ?? '')) ?>" placeholder="Ex.: OPERATIONAL">
        </div>
        <div class="actions">
            <button type="submit">Aplicar filtros</button>
            <a class="button button-secondary" href="<?= e(url('/operational/governanca')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Frequencia por acao</h2>
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
                <?php foreach ($actionFrequency as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['modulo_codigo'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['acao'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['resultado'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($actionFrequency === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem eventos para os filtros informados.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <h2>Termo vigente</h2>
        <p><strong><?= e((string) ($term['title'] ?? 'Termo de Governanca')) ?></strong></p>
        <p class="muted">Codigo: <?= e((string) ($term['code'] ?? 'OPER_GOV_BASE')) ?> | Versao: <?= e((string) ($term['version'] ?? 'N/A')) ?></p>
        <p><?= e((string) ($term['description'] ?? '')) ?></p>
        <p class="muted">Escopo ativo: <?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>

        <?php if ($termAccepted): ?>
            <div class="alert alert-success">Termo vigente ja aceito para o usuario atual.</div>
        <?php else: ?>
            <form method="post" action="<?= e(url('/operational/governanca/termo-aceite')) ?>" data-guard-submit="true">
                <?= App\Support\Csrf::field('operational_governance_term_accept') ?>
                <button type="submit">
                    <span class="button-text">Aceitar termo vigente</span>
                    <span class="button-loading" hidden>Processando...</span>
                </button>
            </form>
        <?php endif; ?>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Logs recentes</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data/hora</th>
                <th>Modulo</th>
                <th>Acao</th>
                <th>Resultado</th>
                <th>Entidade</th>
                <th>Usuario</th>
                <th>IP</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentLogs as $row): ?>
                <tr>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($row['modulo_codigo'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['acao'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['resultado'] ?? '-')) ?></td>
                    <td>
                        <?= e((string) ($row['entidade_tipo'] ?? '-')) ?>
                        <?php if (!empty($row['entidade_id'])): ?>
                            #<?= e((string) $row['entidade_id']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= e((string) ($row['usuario_nome'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['ip_address'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentLogs === []): ?>
                <tr>
                    <td colspan="7" class="muted">Sem logs para os filtros selecionados.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card mt-1">
    <h2>Aceites recentes de termo</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data/hora</th>
                <th>Usuario</th>
                <th>Termo</th>
                <th>Versao</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentTermAcceptances as $row): ?>
                <tr>
                    <td><?= e((string) ($row['aceito_em'] ?? '')) ?></td>
                    <td><?= e((string) ($row['usuario_nome'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['termo_codigo'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['versao_termo'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentTermAcceptances === []): ?>
                <tr>
                    <td colspan="4" class="muted">Nenhum aceite registrado no escopo atual.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

