<?php

declare(strict_types=1);

$scope = $scope ?? [];
$filters = $filters ?? [];
$attachments = $attachments ?? [];
$attachmentsByEntity = $attachmentsByEntity ?? [];
$incidentOptions = $incidentOptions ?? [];
$planconOptions = $planconOptions ?? [];
$incidentRecordOptions = $incidentRecordOptions ?? [];
$planconRiskOptions = $planconRiskOptions ?? [];
?>
<section class="hero">
    <h1>Documentos Operacionais</h1>
    <p>Anexos institucionais com rastreabilidade por entidade, escopo e usuario responsavel.</p>
</section>

<section class="grid grid-2">
    <article class="card">
        <h2>Novo anexo</h2>
        <form method="post" action="<?= e(url('/operational/documentos/upload')) ?>" enctype="multipart/form-data" data-guard-submit="true">
            <?= App\Support\Csrf::field('operational_document_upload') ?>
            <div class="field">
                <label for="doc_entidade_ref">Vincular a entidade (recomendado)</label>
                <select id="doc_entidade_ref" name="entidade_ref">
                    <option value="">Selecione</option>
                    <?php foreach ($incidentOptions as $item): ?>
                        <option value="<?= e('incidentes:' . (string) $item['id']) ?>">
                            INCIDENTE #<?= e((string) $item['id']) ?> - <?= e((string) $item['numero_ocorrencia']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php foreach ($planconOptions as $item): ?>
                        <option value="<?= e('plancons:' . (string) $item['id']) ?>">
                            PLANCON #<?= e((string) $item['id']) ?> - <?= e((string) $item['titulo_plano']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php foreach ($incidentRecordOptions as $item): ?>
                        <option value="<?= e('incidentes_registros_operacionais:' . (string) $item['id']) ?>">
                            REGISTRO #<?= e((string) $item['id']) ?> - <?= e((string) $item['numero_ocorrencia']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php foreach ($planconRiskOptions as $item): ?>
                        <option value="<?= e('plancon_riscos:' . (string) $item['id']) ?>">
                            RISCO PLANCON #<?= e((string) $item['id']) ?> - <?= e((string) ($item['titulo_plano'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="doc_entidade_tipo">Entidade tipo (alternativo manual)</label>
                <select id="doc_entidade_tipo" name="entidade_tipo">
                    <option value="">Nao informar</option>
                    <option value="incidentes">incidentes</option>
                    <option value="plancons">plancons</option>
                    <option value="incidentes_registros_operacionais">incidentes_registros_operacionais</option>
                    <option value="plancon_riscos">plancon_riscos</option>
                </select>
            </div>
            <div class="field">
                <label for="doc_entidade_id">Entidade ID (alternativo manual)</label>
                <input id="doc_entidade_id" name="entidade_id" type="number" min="1" placeholder="Ex.: 125">
            </div>
            <div class="field">
                <label for="doc_arquivo">Arquivo</label>
                <input id="doc_arquivo" name="arquivo" type="file" required>
            </div>
            <div class="field">
                <label for="doc_visibilidade">Visibilidade</label>
                <select id="doc_visibilidade" name="visibilidade">
                    <option value="PRIVADO">PRIVADO</option>
                    <option value="INSTITUCIONAL">INSTITUCIONAL</option>
                    <option value="PUBLICO">PUBLICO</option>
                </select>
            </div>
            <button type="submit">
                <span class="button-text">Anexar documento</span>
                <span class="button-loading" hidden>Processando...</span>
            </button>
        </form>
    </article>

    <article class="card table-card">
        <h2>Distribuicao por entidade</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Entidade</th>
                    <th>Total anexos</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($attachmentsByEntity as $row): ?>
                    <tr>
                        <td><?= e((string) ($row['entidade_tipo'] ?? '')) ?></td>
                        <td><?= e((string) ($row['total'] ?? 0)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($attachmentsByEntity === []): ?>
                    <tr>
                        <td colspan="2" class="muted">Sem anexos no escopo atual.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="muted">Escopo ativo: <?= e((string) ($scope['escopo_ativo'] ?? 'N/A')) ?></p>
    </article>
</section>

<section class="card mt-1">
    <h2>Filtro de consulta</h2>
    <form method="get" action="<?= e(url('/operational/documentos')) ?>" class="grid grid-2">
        <div class="field">
            <label for="f_doc_entidade_tipo">Entidade tipo</label>
            <select id="f_doc_entidade_tipo" name="entidade_tipo">
                <option value="">Todos</option>
                <?php foreach (['incidentes', 'plancons', 'incidentes_registros_operacionais', 'plancon_riscos'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= (($filters['entidade_tipo'] ?? null) === $type) ? 'selected' : '' ?>>
                        <?= e($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions">
            <button type="submit">Aplicar filtro</button>
            <a class="button button-secondary" href="<?= e(url('/operational/documentos')) ?>">Limpar</a>
        </div>
    </form>
</section>

<section class="card table-card mt-1">
    <h2>Anexos recentes no escopo</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Entidade</th>
                <th>Arquivo</th>
                <th>MIME</th>
                <th>Tamanho (KB)</th>
                <th>Visibilidade</th>
                <th>Enviado por</th>
                <th>Data</th>
                <th>Acoes</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($attachments as $row): ?>
                <tr>
                    <td><?= e((string) ($row['id'] ?? '')) ?></td>
                    <td>
                        <?= e((string) ($row['entidade_tipo'] ?? '')) ?>
                        #<?= e((string) ($row['entidade_id'] ?? '')) ?>
                    </td>
                    <td><?= e((string) ($row['arquivo_nome'] ?? '')) ?></td>
                    <td><?= e((string) ($row['arquivo_mime'] ?? '')) ?></td>
                    <td><?= e(number_format(((int) ($row['tamanho_bytes'] ?? 0)) / 1024, 1, ',', '.')) ?></td>
                    <td><?= e((string) ($row['visibilidade'] ?? '')) ?></td>
                    <td><?= e((string) ($row['enviado_por'] ?? '-')) ?></td>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td>
                        <a href="<?= e(url('/operational/documentos/download?anexo_id=' . (string) ($row['id'] ?? '0'))) ?>">Baixar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($attachments === []): ?>
                <tr>
                    <td colspan="9" class="muted">Nenhum anexo encontrado para o escopo/filtro atual.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="muted">Anexos privados podem ser baixados apenas pelo autor ou perfis com permissao ampliada.</p>
</section>
