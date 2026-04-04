<?php

declare(strict_types=1);

$scope = $scope ?? [];
$incidents = $incidents ?? [];
$incidentOptions = $incidentOptions ?? [];
$periodOptions = $periodOptions ?? [];
$recentRecords = $recentRecords ?? [];
$statusOptions = $statusOptions ?? [];
$commandStatusOptions = $commandStatusOptions ?? [];
$periodStatusOptions = $periodStatusOptions ?? [];
$recordTypeOptions = $recordTypeOptions ?? [];
$recordStatusOptions = $recordStatusOptions ?? [];
$criticalityOptions = $criticalityOptions ?? [];
$classificationOptions = $classificationOptions ?? [];
?>
<section class="hero">
    <h1>Gestao de Incidentes</h1>
    <p>Ciclo minimo da resposta operacional: abertura, briefing, comando inicial, periodos e registros.</p>
</section>

<section class="grid grid-2">
    <article class="card">
        <h2>Abertura de incidente</h2>
        <form method="post" action="<?= e(url('/operational/incidentes')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_incidente_create') ?>
            <div class="field">
                <label for="inc_numero_ocorrencia">Numero da ocorrencia (opcional)</label>
                <input id="inc_numero_ocorrencia" name="numero_ocorrencia" type="text" placeholder="INC-1-20260404153000-001">
            </div>
            <div class="field">
                <label for="inc_nome_incidente">Nome do incidente</label>
                <input id="inc_nome_incidente" name="nome_incidente" type="text" required>
            </div>
            <div class="field">
                <label for="inc_tipo_ocorrencia">Tipo de ocorrencia</label>
                <input id="inc_tipo_ocorrencia" name="tipo_ocorrencia" type="text" required placeholder="INUNDACAO, INCENDIO, DESLIZAMENTO">
            </div>
            <div class="field">
                <label for="inc_classificacao">Classificacao inicial</label>
                <select id="inc_classificacao" name="classificacao_inicial">
                    <option value="">Nao informado</option>
                    <?php foreach ($classificationOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="inc_data_hora_acionamento">Data/hora do acionamento</label>
                <input id="inc_data_hora_acionamento" name="data_hora_acionamento" type="datetime-local">
            </div>
            <div class="field">
                <label for="inc_data_hora_abertura">Data/hora de abertura</label>
                <input id="inc_data_hora_abertura" name="data_hora_abertura" type="datetime-local" value="<?= e((string) date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="field">
                <label for="inc_status">Status inicial</label>
                <select id="inc_status" name="status_incidente">
                    <?php foreach ($statusOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (($scope['restrict_to_unidade'] ?? false) !== true): ?>
                <div class="field">
                    <label for="inc_unidade_id">Unidade (opcional)</label>
                    <input id="inc_unidade_id" name="unidade_id" type="number" min="1" placeholder="ID da unidade">
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="inc_municipio">Municipio</label>
                <input id="inc_municipio" name="municipio" type="text">
            </div>
            <div class="field">
                <label for="inc_local_detalhado">Local detalhado</label>
                <input id="inc_local_detalhado" name="local_detalhado" type="text">
            </div>
            <div class="field">
                <label for="inc_coordenadas">Coordenadas (opcional)</label>
                <input id="inc_coordenadas" name="coordenadas" type="text" placeholder="-10.1840,-48.3336">
            </div>
            <div class="field">
                <label for="inc_descricao_inicial">Descricao inicial</label>
                <textarea id="inc_descricao_inicial" name="descricao_inicial" rows="4" required></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Abrir incidente</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Briefing inicial</h2>
        <form method="post" action="<?= e(url('/operational/incidentes/briefing')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_briefing_create') ?>
            <div class="field">
                <label for="briefing_incidente_id">Incidente</label>
                <select id="briefing_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="briefing_resumo">Resumo da situacao</label>
                <textarea id="briefing_resumo" name="resumo_situacao" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="briefing_objetivos">Objetivos iniciais</label>
                <textarea id="briefing_objetivos" name="objetivos_iniciais" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="briefing_acoes">Acoes atuais</label>
                <textarea id="briefing_acoes" name="acoes_atuais" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="briefing_necessidades">Necessidades imediatas</label>
                <textarea id="briefing_necessidades" name="necessidades_imediatas" rows="2"></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Registrar briefing</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Comando inicial</h2>
        <form method="post" action="<?= e(url('/operational/incidentes/comando')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_comando_upsert') ?>
            <div class="field">
                <label for="cmd_incidente_id">Incidente</label>
                <select id="cmd_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="cmd_tipo">Tipo de comando</label>
                <select id="cmd_tipo" name="tipo_comando">
                    <option value="UNICO">UNICO</option>
                    <option value="UNIFICADO">UNIFICADO</option>
                </select>
            </div>
            <div class="field">
                <label for="cmd_status">Status do comando</label>
                <select id="cmd_status" name="status_comando">
                    <?php foreach ($commandStatusOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="cmd_nome">Comandante</label>
                <input id="cmd_nome" name="comandante_nome" type="text">
            </div>
            <div class="field">
                <label for="cmd_instituicao">Instituicao</label>
                <input id="cmd_instituicao" name="instituicao_comandante" type="text">
            </div>
            <div class="field">
                <label for="cmd_local_posto">Local do posto de comando</label>
                <input id="cmd_local_posto" name="local_posto_comando" type="text">
            </div>
            <div class="field">
                <label for="cmd_diretrizes">Diretrizes institucionais</label>
                <textarea id="cmd_diretrizes" name="diretrizes_institucionais" rows="2"></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Salvar comando</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Periodo operacional</h2>
        <form method="post" action="<?= e(url('/operational/incidentes/periodos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_periodo_create') ?>
            <div class="field">
                <label for="periodo_incidente_id">Incidente</label>
                <select id="periodo_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="periodo_numero">Numero do periodo (opcional)</label>
                <input id="periodo_numero" name="numero_periodo" type="number" min="1">
            </div>
            <div class="field">
                <label for="periodo_inicio">Inicio</label>
                <input id="periodo_inicio" name="data_hora_inicio" type="datetime-local" value="<?= e((string) date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="field">
                <label for="periodo_status">Status</label>
                <select id="periodo_status" name="status_periodo">
                    <?php foreach ($periodStatusOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="periodo_objetivos">Objetivos do periodo</label>
                <textarea id="periodo_objetivos" name="objetivos_periodo" rows="2"></textarea>
            </div>
            <div class="field">
                <label for="periodo_recursos">Recursos principais do periodo</label>
                <textarea id="periodo_recursos" name="recursos_principais_periodo" rows="2"></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Abrir periodo</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card">
        <h2>Registro operacional / Diario</h2>
        <form method="post" action="<?= e(url('/operational/incidentes/registros')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_registro_create') ?>
            <div class="field">
                <label for="registro_incidente_id">Incidente</label>
                <select id="registro_incidente_id" name="incidente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - <?= e((string) $item['nome_incidente']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="registro_periodo_id">Periodo operacional (opcional)</label>
                <select id="registro_periodo_id" name="periodo_operacional_id">
                    <option value="">Sem periodo</option>
                    <?php foreach ($periodOptions as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>">
                            <?= e((string) $item['numero_ocorrencia']) ?> - P<?= e((string) $item['numero_periodo']) ?> (<?= e((string) $item['status_periodo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="registro_tipo">Tipo de registro</label>
                <select id="registro_tipo" name="tipo_registro">
                    <?php foreach ($recordTypeOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="registro_status">Status do registro</label>
                <select id="registro_status" name="status_registro">
                    <?php foreach ($recordStatusOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="registro_criticidade">Criticidade</label>
                <select id="registro_criticidade" name="criticidade">
                    <?php foreach ($criticalityOptions as $item): ?>
                        <option value="<?= e((string) $item) ?>"><?= e((string) $item) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="registro_titulo">Titulo</label>
                <input id="registro_titulo" name="titulo_registro" type="text" required>
            </div>
            <div class="field">
                <label for="registro_descricao">Descricao</label>
                <textarea id="registro_descricao" name="descricao_registro" rows="3" required></textarea>
            </div>
            <div class="field">
                <label for="registro_origem">Origem da informacao</label>
                <input id="registro_origem" name="origem_informacao" type="text">
            </div>
            <div class="field">
                <label for="registro_encaminhamento">Encaminhamento</label>
                <textarea id="registro_encaminhamento" name="encaminhamento" rows="2"></textarea>
            </div>
            <button type="submit">
                <span class="button-text">Salvar registro</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>
</section>

<section class="card table-card mt-1">
    <h2>Incidentes no seu escopo</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Numero</th>
                <th>Incidente</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Classificacao</th>
                <th>Briefing</th>
                <th>Periodo ativo</th>
                <th>Abertura</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($incidents as $row): ?>
                <tr>
                    <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                    <td><?= e((string) $row['nome_incidente']) ?></td>
                    <td><?= e((string) $row['tipo_ocorrencia']) ?></td>
                    <td><span class="tag"><?= e((string) $row['status_incidente']) ?></span></td>
                    <td><?= e((string) ($row['classificacao_inicial'] ?? 'N/A')) ?></td>
                    <td><?= e((string) ($row['ultima_versao_briefing'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['periodo_ativo_numero'] ?? '-')) ?></td>
                    <td><?= e((string) $row['data_hora_abertura']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($incidents === []): ?>
                <tr>
                    <td colspan="8" class="muted">Nenhum incidente cadastrado no escopo atual.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card mt-1">
    <h2>Ultimos registros operacionais</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Data/hora</th>
                <th>Incidente</th>
                <th>Tipo</th>
                <th>Titulo</th>
                <th>Criticidade</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentRecords as $row): ?>
                <tr>
                    <td><?= e((string) $row['data_hora_registro']) ?></td>
                    <td><?= e((string) $row['numero_ocorrencia']) ?></td>
                    <td><?= e((string) $row['tipo_registro']) ?></td>
                    <td><?= e((string) $row['titulo_registro']) ?></td>
                    <td><?= e((string) $row['criticidade']) ?></td>
                    <td><?= e((string) $row['status_registro']) ?></td>
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
