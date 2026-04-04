<?php

declare(strict_types=1);

$scope = $scope ?? [];
$summary = $summary ?? [];
$incidentOptions = $incidentOptions ?? [];
$periodOptions = $periodOptions ?? [];
$recentPai = $recentPai ?? [];
$recentOperations = $recentOperations ?? [];
$recentPlanning = $recentPlanning ?? [];
$recentSafety = $recentSafety ?? [];
$recentDemobilization = $recentDemobilization ?? [];
?>
<section class="hero">
    <h1>Gerenciamento de Desastres</h1>
    <p>Expansao da Fase 3 para PAI, operacoes, planejamento, seguranca e desmobilizacao.</p>
</section>

<section class="grid">
    <article class="card kpi-card">
        <h2>PAI</h2>
        <p class="kpi-value"><?= e((string) ($summary['pai'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Operacoes</h2>
        <p class="kpi-value"><?= e((string) ($summary['operacoes'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Planejamento</h2>
        <p class="kpi-value"><?= e((string) ($summary['planejamento'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Seguranca</h2>
        <p class="kpi-value"><?= e((string) ($summary['seguranca'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Desmobilizacao</h2>
        <p class="kpi-value"><?= e((string) ($summary['desmobilizacao'] ?? 0)) ?></p>
    </article>
    <article class="card">
        <h2>Escopo ativo</h2>
        <p class="kpi-value"><?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card">
        <h2>PAI do incidente</h2>
        <form method="post" action="<?= e(url('/operational/desastres/pai')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_disaster_pai_create') ?>
            <div class="field">
                <label for="pai_incidente_id">Incidente</label>
                <select id="pai_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="pai_periodo_operacional_id">Periodo operacional (opcional)</label>
                <select id="pai_periodo_operacional_id" name="periodo_operacional_id">
                    <option value="">Sem periodo</option>
                    <?php foreach ($periodOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - P<?= e((string) $item['numero_periodo']) ?> (<?= e((string) $item['status_periodo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="pai_versao_pai">Versao</label>
                <input id="pai_versao_pai" name="versao_pai" type="text" required placeholder="PAI-INC-2026-0001-V2">
            </div>
            <div class="field">
                <label for="pai_estrategia_geral">Estrategia geral</label>
                <textarea id="pai_estrategia_geral" name="estrategia_geral" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="pai_status_pai">Status</label>
                <select id="pai_status_pai" name="status_pai">
                    <option value="PROPOSTO">PROPOSTO</option>
                    <option value="APROVADO">APROVADO</option>
                    <option value="EM_EXECUCAO">EM_EXECUCAO</option>
                    <option value="ENCERRADO">ENCERRADO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar PAI</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Operacao de campo</h2>
        <form method="post" action="<?= e(url('/operational/desastres/operacoes')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_disaster_operation_create') ?>
            <div class="field">
                <label for="op_incidente_id">Incidente</label>
                <select id="op_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="op_periodo_operacional_id">Periodo operacional (opcional)</label>
                <select id="op_periodo_operacional_id" name="periodo_operacional_id">
                    <option value="">Sem periodo</option>
                    <?php foreach ($periodOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - P<?= e((string) $item['numero_periodo']) ?> (<?= e((string) $item['status_periodo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="op_frente_operacional">Frente operacional</label>
                <input id="op_frente_operacional" name="frente_operacional" type="text" required>
            </div>
            <div class="field">
                <label for="op_setor_operacional">Setor</label>
                <input id="op_setor_operacional" name="setor_operacional" type="text">
            </div>
            <div class="field">
                <label for="op_missao_tatica">Missao tatica</label>
                <textarea id="op_missao_tatica" name="missao_tatica" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="op_status_operacao">Status</label>
                <select id="op_status_operacao" name="status_operacao">
                    <option value="ATIVA">ATIVA</option>
                    <option value="PAUSADA">PAUSADA</option>
                    <option value="ENCERRADA">ENCERRADA</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar operacao</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Planejamento situacional</h2>
        <form method="post" action="<?= e(url('/operational/desastres/planejamento')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_disaster_planning_create') ?>
            <div class="field">
                <label for="pl_incidente_id">Incidente</label>
                <select id="pl_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="pl_periodo_operacional_id">Periodo operacional (opcional)</label>
                <select id="pl_periodo_operacional_id" name="periodo_operacional_id">
                    <option value="">Sem periodo</option>
                    <?php foreach ($periodOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - P<?= e((string) $item['numero_periodo']) ?> (<?= e((string) $item['status_periodo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="pl_situacao_consolidada">Situacao consolidada</label>
                <textarea id="pl_situacao_consolidada" name="situacao_consolidada" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="pl_prognostico">Prognostico</label>
                <textarea id="pl_prognostico" name="prognostico" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="pl_status_planejamento">Status</label>
                <select id="pl_status_planejamento" name="status_planejamento">
                    <option value="EM_ANALISE">EM_ANALISE</option>
                    <option value="VALIDADO">VALIDADO</option>
                    <option value="ARQUIVADO">ARQUIVADO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar planejamento</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Seguranca operacional</h2>
        <form method="post" action="<?= e(url('/operational/desastres/seguranca')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_disaster_safety_create') ?>
            <div class="field">
                <label for="sg_incidente_id">Incidente</label>
                <select id="sg_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="sg_periodo_operacional_id">Periodo operacional (opcional)</label>
                <select id="sg_periodo_operacional_id" name="periodo_operacional_id">
                    <option value="">Sem periodo</option>
                    <?php foreach ($periodOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - P<?= e((string) $item['numero_periodo']) ?> (<?= e((string) $item['status_periodo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="sg_riscos_operacionais">Riscos operacionais</label>
                <textarea id="sg_riscos_operacionais" name="riscos_operacionais" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="sg_medidas_controle">Medidas de controle</label>
                <textarea id="sg_medidas_controle" name="medidas_controle" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="sg_status_seguranca">Status</label>
                <select id="sg_status_seguranca" name="status_seguranca">
                    <option value="ATIVA">ATIVA</option>
                    <option value="EM_ALERTA">EM_ALERTA</option>
                    <option value="ENCERRADA">ENCERRADA</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar seguranca</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Desmobilizacao</h2>
        <form method="post" action="<?= e(url('/operational/desastres/desmobilizacao')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_disaster_demobilization_create') ?>
            <div class="field">
                <label for="dm_incidente_id">Incidente</label>
                <select id="dm_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="dm_criterios_desmobilizacao">Criterios de desmobilizacao</label>
                <textarea id="dm_criterios_desmobilizacao" name="criterios_desmobilizacao" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="dm_data_hora_inicio">Data/hora de inicio</label>
                <input id="dm_data_hora_inicio" name="data_hora_inicio" type="datetime-local">
            </div>
            <div class="field">
                <label for="dm_data_hora_encerramento">Data/hora de encerramento</label>
                <input id="dm_data_hora_encerramento" name="data_hora_encerramento" type="datetime-local">
            </div>
            <div class="field">
                <label for="dm_status_desmobilizacao">Status</label>
                <select id="dm_status_desmobilizacao" name="status_desmobilizacao">
                    <option value="PLANEJADA">PLANEJADA</option>
                    <option value="EM_ANDAMENTO">EM_ANDAMENTO</option>
                    <option value="CONCLUIDA">CONCLUIDA</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar desmobilizacao</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Ultimos PAI</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Incidente</th>
                    <th>Versao</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentPai as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                        <td><?= e((string) $row['versao_pai']) ?></td>
                        <td><span class="tag"><?= e((string) $row['status_pai']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentPai === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem PAI registrados no escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Ultimas operacoes</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Incidente</th>
                    <th>Frente</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentOperations as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                        <td><?= e((string) $row['frente_operacional']) ?></td>
                        <td><span class="tag"><?= e((string) $row['status_operacao']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentOperations === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem operacoes registradas no escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Ultimos planejamentos</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Incidente</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentPlanning as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                        <td><span class="tag"><?= e((string) $row['status_planejamento']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentPlanning === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Sem planejamentos registrados no escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Ultimos registros de seguranca</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Incidente</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentSafety as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                        <td><span class="tag"><?= e((string) $row['status_seguranca']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentSafety === []): ?>
                    <tr>
                        <td colspan="3" class="muted">Sem registros de seguranca no escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Ultimas desmobilizacoes</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data</th>
                <th>Incidente</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentDemobilization as $row): ?>
                <tr>
                    <td><?= e((string) $row['created_at']) ?></td>
                    <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                    <td><span class="tag"><?= e((string) $row['status_desmobilizacao']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentDemobilization === []): ?>
                <tr>
                    <td colspan="3" class="muted">Sem desmobilizacoes registradas no escopo.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
