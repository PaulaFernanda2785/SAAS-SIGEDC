<?php

declare(strict_types=1);

$scope = $scope ?? [];
$summary = $summary ?? [];
$plancons = $plancons ?? [];
$planconOptions = $planconOptions ?? [];
$recentRisks = $recentRisks ?? [];
$recentScenarios = $recentScenarios ?? [];
$recentResources = $recentResources ?? [];
?>
<section class="hero">
    <h1>PLANCON e Gestao de Riscos</h1>
    <p>Expansao da Fase 3: plano, riscos, cenarios, ativacao, recursos e revisoes.</p>
</section>

<section class="grid">
    <article class="card kpi-card">
        <h2>Total de planos</h2>
        <p class="kpi-value"><?= e((string) ($summary['total_plancons'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Planos ativos</h2>
        <p class="kpi-value"><?= e((string) ($summary['plancons_ativos'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Em revisao</h2>
        <p class="kpi-value"><?= e((string) ($summary['plancons_em_revisao'] ?? 0)) ?></p>
    </article>
    <article class="card kpi-card">
        <h2>Vencidos</h2>
        <p class="kpi-value"><?= e((string) ($summary['plancons_vencidos'] ?? 0)) ?></p>
    </article>
</section>

<section class="grid grid-2 mt-1">
    <article class="card">
        <h2>Novo PLANCON</h2>
        <form method="post" action="<?= e(url('/operational/plancon')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_create') ?>
            <div class="field">
                <label for="plancon_titulo_plano">Titulo do plano</label>
                <input id="plancon_titulo_plano" name="titulo_plano" type="text" required>
            </div>
            <?php if (($scope['restrict_to_unidade'] ?? false) !== true): ?>
                <div class="field">
                    <label for="plancon_unidade_id">Unidade (opcional)</label>
                    <input id="plancon_unidade_id" name="unidade_id" type="number" min="1">
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="plancon_versao_documento">Versao</label>
                <input id="plancon_versao_documento" name="versao_documento" type="text" placeholder="v1.0">
            </div>
            <div class="field">
                <label for="plancon_municipio_estado">Municipio/UF</label>
                <input id="plancon_municipio_estado" name="municipio_estado" type="text">
            </div>
            <div class="field">
                <label for="plancon_tipo_desastre_principal">Tipo principal</label>
                <input id="plancon_tipo_desastre_principal" name="tipo_desastre_principal" type="text" placeholder="INUNDACAO">
            </div>
            <div class="field">
                <label for="plancon_status_plancon">Status</label>
                <select id="plancon_status_plancon" name="status_plancon">
                    <option value="RASCUNHO">RASCUNHO</option>
                    <option value="ATIVO">ATIVO</option>
                    <option value="EM_REVISAO">EM_REVISAO</option>
                    <option value="ARQUIVADO">ARQUIVADO</option>
                    <option value="VENCIDO">VENCIDO</option>
                </select>
            </div>
            <div class="field">
                <label for="plancon_vigencia_inicio">Vigencia inicio</label>
                <input id="plancon_vigencia_inicio" name="vigencia_inicio" type="date">
            </div>
            <div class="field">
                <label for="plancon_vigencia_fim">Vigencia fim</label>
                <input id="plancon_vigencia_fim" name="vigencia_fim" type="date">
            </div>
            <div class="field">
                <label for="plancon_objetivo_geral">Objetivo geral</label>
                <textarea id="plancon_objetivo_geral" name="objetivo_geral" rows="3"></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Criar PLANCON</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Risco do PLANCON</h2>
        <form method="post" action="<?= e(url('/operational/plancon/riscos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_risk_create') ?>
            <div class="field">
                <label for="risco_plancon_id">PLANCON</label>
                <select id="risco_plancon_id" name="plancon_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['titulo_plano']) ?> (<?= e((string) ($item['versao_documento'] ?? 'sem versao')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="risco_tipo_ameaca">Tipo de ameaca</label>
                <input id="risco_tipo_ameaca" name="tipo_ameaca" type="text" placeholder="HIDROLOGICA">
            </div>
            <div class="field">
                <label for="risco_descricao_risco">Descricao do risco</label>
                <textarea id="risco_descricao_risco" name="descricao_risco" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="risco_nivel_risco">Nivel</label>
                <select id="risco_nivel_risco" name="nivel_risco">
                    <option value="">Nao informado</option>
                    <option value="BAIXO">BAIXO</option>
                    <option value="MODERADO">MODERADO</option>
                    <option value="ALTO">ALTO</option>
                    <option value="MUITO_ALTO">MUITO_ALTO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar risco</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Cenario do PLANCON</h2>
        <form method="post" action="<?= e(url('/operational/plancon/cenarios')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_scenario_create') ?>
            <div class="field">
                <label for="cenario_plancon_id">PLANCON</label>
                <select id="cenario_plancon_id" name="plancon_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['titulo_plano']) ?> (<?= e((string) ($item['versao_documento'] ?? 'sem versao')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="cenario_nome">Nome do cenario</label>
                <input id="cenario_nome" name="nome_cenario" type="text" required>
            </div>
            <div class="field">
                <label for="cenario_tipo">Tipo de desastre associado</label>
                <input id="cenario_tipo" name="tipo_desastre_associado" type="text">
            </div>
            <div class="field">
                <label for="cenario_descricao">Descricao do cenario</label>
                <textarea id="cenario_descricao" name="descricao_cenario" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="cenario_classificacao">Classificacao</label>
                <select id="cenario_classificacao" name="classificacao_cenario">
                    <option value="">Nao informado</option>
                    <option value="BAIXA">BAIXA</option>
                    <option value="MODERADA">MODERADA</option>
                    <option value="ALTA">ALTA</option>
                    <option value="CRITICA">CRITICA</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar cenario</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Nivel de ativacao</h2>
        <form method="post" action="<?= e(url('/operational/plancon/ativacao')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_activation_create') ?>
            <div class="field">
                <label for="ativacao_plancon_id">PLANCON</label>
                <select id="ativacao_plancon_id" name="plancon_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['titulo_plano']) ?> (<?= e((string) ($item['versao_documento'] ?? 'sem versao')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="ativacao_nivel_operacional">Nivel operacional</label>
                <input id="ativacao_nivel_operacional" name="nivel_operacional" type="text" required placeholder="NIVEL 2 - ALERTA">
            </div>
            <div class="field">
                <label for="ativacao_criterios">Criterios de ativacao</label>
                <textarea id="ativacao_criterios" name="criterios_ativacao" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="ativacao_status">Status</label>
                <select id="ativacao_status" name="status_nivel">
                    <option value="ATIVO">ATIVO</option>
                    <option value="INATIVO">INATIVO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar nivel</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Recurso do PLANCON</h2>
        <form method="post" action="<?= e(url('/operational/plancon/recursos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_resource_create') ?>
            <div class="field">
                <label for="recurso_plancon_id">PLANCON</label>
                <select id="recurso_plancon_id" name="plancon_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['titulo_plano']) ?> (<?= e((string) ($item['versao_documento'] ?? 'sem versao')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="recurso_tipo_recurso">Tipo de recurso</label>
                <input id="recurso_tipo_recurso" name="tipo_recurso" type="text" required placeholder="EQUIPAMENTO">
            </div>
            <div class="field">
                <label for="recurso_categoria_recurso">Categoria</label>
                <input id="recurso_categoria_recurso" name="categoria_recurso" type="text">
            </div>
            <div class="field">
                <label for="recurso_descricao_recurso">Descricao</label>
                <textarea id="recurso_descricao_recurso" name="descricao_recurso" rows="2" required></textarea>
            </div>
            <div class="field">
                <label for="recurso_quantidade_disponivel">Quantidade</label>
                <input id="recurso_quantidade_disponivel" name="quantidade_disponivel" type="text" placeholder="3">
            </div>
            <div class="field">
                <label for="recurso_status_recurso">Status</label>
                <select id="recurso_status_recurso" name="status_recurso">
                    <option value="DISPONIVEL">DISPONIVEL</option>
                    <option value="INDISPONIVEL">INDISPONIVEL</option>
                    <option value="EM_MANUTENCAO">EM_MANUTENCAO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Salvar recurso</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Revisao do PLANCON</h2>
        <form method="post" action="<?= e(url('/operational/plancon/revisoes')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_plancon_review_create') ?>
            <div class="field">
                <label for="revisao_plancon_id">PLANCON</label>
                <select id="revisao_plancon_id" name="plancon_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['titulo_plano']) ?> (<?= e((string) ($item['versao_documento'] ?? 'sem versao')) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="revisao_versao_revisao">Versao da revisao</label>
                <input id="revisao_versao_revisao" name="versao_revisao" type="text" required placeholder="REV-2026-02">
            </div>
            <div class="field">
                <label for="revisao_motivo">Motivo</label>
                <textarea id="revisao_motivo" name="motivo_revisao" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="revisao_status_revisao">Status</label>
                <select id="revisao_status_revisao" name="status_revisao">
                    <option value="RASCUNHO">RASCUNHO</option>
                    <option value="EM_ANALISE">EM_ANALISE</option>
                    <option value="APROVADA">APROVADA</option>
                    <option value="REPROVADA">REPROVADA</option>
                </select>
            </div>
            <div class="field">
                <label for="revisao_data_revisao">Data revisao</label>
                <input id="revisao_data_revisao" name="data_revisao" type="date">
            </div>
            <button type="submit">
                <span class="button-text">Salvar revisao</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>PLANCONs no escopo</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Titulo</th>
                <th>Versao</th>
                <th>Municipio/UF</th>
                <th>Tipo principal</th>
                <th>Status</th>
                <th>Vigencia</th>
                <th>Atualizado em</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($plancons as $row): ?>
                <tr>
                    <td><?= e((string) $row['titulo_plano']) ?></td>
                    <td><?= e((string) ($row['versao_documento'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['municipio_estado'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['tipo_desastre_principal'] ?? '-')) ?></td>
                    <td><span class="tag"><?= e((string) $row['status_plancon']) ?></span></td>
                    <td>
                        <?= e((string) ($row['vigencia_inicio'] ?? '-')) ?>
                        ate
                        <?= e((string) ($row['vigencia_fim'] ?? '-')) ?>
                    </td>
                    <td><?= e((string) $row['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($plancons === []): ?>
                <tr>
                    <td colspan="7" class="muted">Nenhum PLANCON encontrado no escopo atual.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="grid grid-2 mt-1">
    <article class="card table-card">
        <h2>Ultimos riscos registrados</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>PLANCON</th>
                    <th>Ameaca</th>
                    <th>Nivel</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentRisks as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['titulo_plano']) ?></td>
                        <td><?= e((string) ($row['tipo_ameaca'] ?? '-')) ?></td>
                        <td><?= e((string) ($row['nivel_risco'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentRisks === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem riscos registrados neste escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card table-card">
        <h2>Ultimos cenarios registrados</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th>PLANCON</th>
                    <th>Cenario</th>
                    <th>Classificacao</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentScenarios as $row): ?>
                    <tr>
                        <td><?= e((string) $row['created_at']) ?></td>
                        <td><?= e((string) $row['titulo_plano']) ?></td>
                        <td><?= e((string) $row['nome_cenario']) ?></td>
                        <td><?= e((string) ($row['classificacao_cenario'] ?? '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($recentScenarios === []): ?>
                    <tr>
                        <td colspan="4" class="muted">Sem cenarios registrados neste escopo.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Ultimos recursos cadastrados</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data</th>
                <th>PLANCON</th>
                <th>Tipo</th>
                <th>Categoria</th>
                <th>Quantidade</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentResources as $row): ?>
                <tr>
                    <td><?= e((string) $row['created_at']) ?></td>
                    <td><?= e((string) $row['titulo_plano']) ?></td>
                    <td><?= e((string) $row['tipo_recurso']) ?></td>
                    <td><?= e((string) ($row['categoria_recurso'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['quantidade_disponivel'] ?? '-')) ?> <?= e((string) ($row['unidade_medida'] ?? '')) ?></td>
                    <td><span class="tag"><?= e((string) $row['status_recurso']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($recentResources === []): ?>
                <tr>
                    <td colspan="6" class="muted">Sem recursos registrados neste escopo.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
