<?php

declare(strict_types=1);

$summary = $summary ?? [];
$featureFlags = $featureFlags ?? [];
$apiApps = $apiApps ?? [];
$integracoes = $integracoes ?? [];
$automacoes = $automacoes ?? [];
$slaPolicies = $slaPolicies ?? [];
$tickets = $tickets ?? [];
$digitalSignatures = $digitalSignatures ?? [];
$executiveReports = $executiveReports ?? [];
$options = $options ?? [];
$currentUfFilter = $currentUfFilter ?? null;
$canSelectAllUf = $canSelectAllUf ?? false;
$capabilities = $capabilities ?? [];
?>
<section class="hero">
    <h1>Escala Enterprise</h1>
    <p>Gestao de recursos enterprise: API controlada, integracoes externas, automacoes, SLA/suporte, assinatura digital e relatorios executivos.</p>
</section>

<section class="grid grid-2">
    <article class="card">
        <h2>Resumo rapido</h2>
        <p><strong>Features ativas:</strong> <?= e((string) ($summary['features_ativas'] ?? 0)) ?></p>
        <p><strong>Apps API ativas:</strong> <?= e((string) ($summary['api_apps_ativas'] ?? 0)) ?></p>
        <p><strong>Integracoes ativas:</strong> <?= e((string) ($summary['integracoes_ativas'] ?? 0)) ?></p>
        <p><strong>Automacoes ativas:</strong> <?= e((string) ($summary['automacoes_ativas'] ?? 0)) ?></p>
        <p><strong>SLA ativas:</strong> <?= e((string) ($summary['sla_ativas'] ?? 0)) ?></p>
        <p><strong>Tickets abertos:</strong> <?= e((string) ($summary['tickets_abertos'] ?? 0)) ?></p>
        <p><strong>Tickets SLA vencido:</strong> <?= e((string) ($summary['tickets_sla_vencido'] ?? 0)) ?></p>
    </article>
    <article class="card">
        <h2>Capacidades no contexto atual</h2>
        <p><strong>Features:</strong> <?= !empty($capabilities['features']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>API controlada:</strong> <?= !empty($capabilities['api']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>Integracoes:</strong> <?= !empty($capabilities['integracoes']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>Automacoes:</strong> <?= !empty($capabilities['automacoes']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>SLA/Support:</strong> <?= !empty($capabilities['sla']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>Assinatura digital:</strong> <?= !empty($capabilities['assinatura_digital']) ? 'SIM' : 'NAO' ?></p>
        <p><strong>Analytics executivo:</strong> <?= !empty($capabilities['analytics']) ? 'SIM' : 'NAO' ?></p>
    </article>
</section>

<section class="card">
    <h2>Filtro territorial</h2>
    <form method="get" action="<?= e(url('/admin/enterprise')) ?>">
        <div class="field">
            <label for="filtro_enterprise_uf">UF</label>
            <select
                id="filtro_enterprise_uf"
                name="uf"
                <?= $canSelectAllUf ? 'data-uf-dynamic="true" data-uf-include-empty="true" data-uf-empty-label="Todos" data-uf-selected="' . e((string) $currentUfFilter) . '"' : 'disabled' ?>
            >
                <option value="">Todos</option>
                <?php foreach (($options['ufs'] ?? []) as $uf): ?>
                    <?php $sigla = (string) $uf['sigla']; ?>
                    <option value="<?= e($sigla) ?>" <?= $sigla === (string) $currentUfFilter ? 'selected' : '' ?>>
                        <?= e($sigla) ?> - <?= e((string) $uf['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!$canSelectAllUf && $currentUfFilter !== null): ?>
                <input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>">
            <?php endif; ?>
        </div>
        <button type="submit">Aplicar filtro</button>
    </form>
</section>

<section class="grid grid-2">
    <article class="card">
        <h2>Feature flag</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/features')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_feature') ?>
            <div class="field">
                <label for="feature_conta_id">Conta</label>
                <select id="feature_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="feature_orgao_id">Orgao (opcional)</label>
                <select id="feature_orgao_id" name="orgao_id">
                    <option value="">Todos</option>
                    <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                        <option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?> - <?= e((string) ($orgao['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="feature_unidade_id">Unidade (opcional)</label>
                <select id="feature_unidade_id" name="unidade_id">
                    <option value="">Todas</option>
                    <?php foreach (($options['unidades'] ?? []) as $unidade): ?>
                        <option value="<?= e((string) $unidade['id']) ?>"><?= e((string) $unidade['nome_unidade']) ?> - <?= e((string) ($unidade['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="feature_code">Feature code</label>
                <input id="feature_code" name="feature_code" type="text" placeholder="MULTI_UNIDADE_EXPANDIDA" required>
            </div>
            <div class="field">
                <label for="feature_status">Status</label>
                <select id="feature_status" name="status_feature">
                    <option value="ATIVA">ATIVA</option>
                    <option value="INATIVA">INATIVA</option>
                </select>
            </div>
            <div class="field">
                <label for="feature_plano">Plano de referencia</label>
                <input id="feature_plano" name="plano_referencia" type="text" placeholder="ENTERPRISE">
            </div>
            <div class="field">
                <label for="feature_config">Configuracao JSON (opcional)</label>
                <textarea id="feature_config" name="configuracoes_json" rows="3" placeholder='{"rollout": 100}'></textarea>
            </div>
            <button type="submit" <?= empty($capabilities['features']) ? 'disabled' : '' ?>>Salvar feature</button>
        </form>
    </article>

    <article class="card">
        <h2>Cliente API</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/api-apps')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_api_app') ?>
            <div class="field">
                <label for="api_conta_id">Conta</label>
                <select id="api_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="api_orgao_id">Orgao (opcional)</label>
                <select id="api_orgao_id" name="orgao_id">
                    <option value="">Todos</option>
                    <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                        <option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="api_unidade_id">Unidade (opcional)</label>
                <select id="api_unidade_id" name="unidade_id">
                    <option value="">Todas</option>
                    <?php foreach (($options['unidades'] ?? []) as $unidade): ?>
                        <option value="<?= e((string) $unidade['id']) ?>"><?= e((string) $unidade['nome_unidade']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="api_nome_app">Nome da app cliente</label>
                <input id="api_nome_app" name="nome_app" type="text" required>
            </div>
            <div class="field">
                <label for="api_escopos">Escopos (csv)</label>
                <input id="api_escopos" name="escopos" type="text" placeholder="READ_EXEC_SUMMARY,READ_ALERTS">
            </div>
            <div class="field">
                <label for="api_rpm">Limite RPM</label>
                <input id="api_rpm" name="limite_rpm" type="number" min="1" max="10000" value="600">
            </div>
            <div class="field">
                <label for="api_expira_em">Expira em (opcional)</label>
                <input id="api_expira_em" name="expira_em" type="datetime-local">
            </div>
            <div class="field">
                <label for="api_status">Status</label>
                <select id="api_status" name="status_app">
                    <option value="ATIVA">ATIVA</option>
                    <option value="BLOQUEADA">BLOQUEADA</option>
                    <option value="REVOGADA">REVOGADA</option>
                </select>
            </div>
            <button type="submit" <?= empty($capabilities['api']) ? 'disabled' : '' ?>>Gerar app API</button>
        </form>
    </article>
</section>
<section class="grid grid-2">
    <article class="card">
        <h2>Integracao externa</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/integracoes')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_integracao') ?>
            <div class="field">
                <label for="int_conta_id">Conta</label>
                <select id="int_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="int_nome">Nome</label><input id="int_nome" name="nome_integracao" type="text" required></div>
            <div class="field"><label for="int_tipo">Tipo</label><select id="int_tipo" name="tipo_integracao"><option value="WEBHOOK">WEBHOOK</option><option value="HTTP_API">HTTP_API</option></select></div>
            <div class="field"><label for="int_endpoint">Endpoint URL</label><input id="int_endpoint" name="endpoint_url" type="url" required></div>
            <div class="field"><label for="int_auth">Auth</label><select id="int_auth" name="auth_tipo"><option value="NENHUMA">NENHUMA</option><option value="BEARER">BEARER</option><option value="BASIC">BASIC</option><option value="HEADER">HEADER</option></select></div>
            <div class="field"><label for="int_cred">Referencia de credencial</label><input id="int_cred" name="credencial_ref" type="text"></div>
            <div class="field"><label for="int_timeout">Timeout (ms)</label><input id="int_timeout" name="timeout_ms" type="number" min="500" value="4000"></div>
            <div class="field"><label for="int_status">Status</label><select id="int_status" name="status_integracao"><option value="ATIVA">ATIVA</option><option value="INATIVA">INATIVA</option></select></div>
            <div class="field"><label for="int_config">Configuracao JSON</label><textarea id="int_config" name="configuracoes_json" rows="2" placeholder='{"event":"incidente.aberto"}'></textarea></div>
            <button type="submit" <?= empty($capabilities['integracoes']) ? 'disabled' : '' ?>>Salvar integracao</button>
        </form>
    </article>

    <article class="card">
        <h2>Automacao</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/automacoes')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_automacao') ?>
            <div class="field">
                <label for="auto_conta_id">Conta</label>
                <select id="auto_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="auto_nome">Nome da regra</label><input id="auto_nome" name="nome_regra" type="text" required></div>
            <div class="field"><label for="auto_evento">Evento</label><input id="auto_evento" name="evento_codigo" type="text" placeholder="INCIDENTE_ABERTO" required></div>
            <div class="field"><label for="auto_acao">Acao</label><select id="auto_acao" name="acao_tipo"><option value="DISPARAR_INTEGRACAO">DISPARAR_INTEGRACAO</option><option value="ABRIR_TICKET">ABRIR_TICKET</option><option value="GERAR_ALERTA">GERAR_ALERTA</option></select></div>
            <div class="field"><label for="auto_condicao">Condicao JSON</label><textarea id="auto_condicao" name="condicao_json" rows="2" placeholder='{"criticidade":"CRITICA"}'></textarea></div>
            <div class="field"><label for="auto_config">Acao JSON</label><textarea id="auto_config" name="acao_config_json" rows="2" placeholder='{"integracao_id":1}'></textarea></div>
            <div class="field"><label for="auto_status">Status</label><select id="auto_status" name="status_regra"><option value="ATIVA">ATIVA</option><option value="INATIVA">INATIVA</option></select></div>
            <button type="submit" <?= empty($capabilities['automacoes']) ? 'disabled' : '' ?>>Salvar automacao</button>
        </form>
    </article>

    <article class="card">
        <h2>Politica SLA</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/sla')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_sla') ?>
            <div class="field">
                <label for="sla_conta_id">Conta</label>
                <select id="sla_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="sla_codigo">Codigo</label><input id="sla_codigo" name="codigo_sla" type="text" placeholder="SLA_PADRAO" required></div>
            <div class="field"><label for="sla_nome">Nome</label><input id="sla_nome" name="nome_sla" type="text" required></div>
            <div class="field"><label for="sla_prioridade">Prioridade</label><select id="sla_prioridade" name="prioridade"><option value="BAIXA">BAIXA</option><option value="MODERADA" selected>MODERADA</option><option value="ALTA">ALTA</option><option value="CRITICA">CRITICA</option></select></div>
            <div class="field"><label for="sla_resp">Tempo resposta (min)</label><input id="sla_resp" name="tempo_resposta_min" type="number" min="1" value="60"></div>
            <div class="field"><label for="sla_resol">Tempo resolucao (min)</label><input id="sla_resol" name="tempo_resolucao_min" type="number" min="1" value="240"></div>
            <div class="field"><label for="sla_status">Status</label><select id="sla_status" name="status_sla"><option value="ATIVA">ATIVA</option><option value="INATIVA">INATIVA</option></select></div>
            <button type="submit" <?= empty($capabilities['sla']) ? 'disabled' : '' ?>>Salvar SLA</button>
        </form>
    </article>

    <article class="card">
        <h2>Ticket de suporte</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/tickets')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_ticket') ?>
            <div class="field">
                <label for="ticket_conta_id">Conta</label>
                <select id="ticket_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="ticket_sla_id">SLA (opcional)</label><select id="ticket_sla_id" name="sla_politica_id"><option value="">Sem SLA</option><?php foreach ($slaPolicies as $sla): ?><option value="<?= e((string) $sla['id']) ?>"><?= e((string) $sla['codigo_sla']) ?> - <?= e((string) $sla['nome_sla']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="ticket_titulo">Titulo</label><input id="ticket_titulo" name="titulo_ticket" type="text" required></div>
            <div class="field"><label for="ticket_desc">Descricao</label><textarea id="ticket_desc" name="descricao_ticket" rows="3" required></textarea></div>
            <div class="field"><label for="ticket_prioridade">Prioridade</label><select id="ticket_prioridade" name="prioridade"><option value="BAIXA">BAIXA</option><option value="MODERADA" selected>MODERADA</option><option value="ALTA">ALTA</option><option value="CRITICA">CRITICA</option></select></div>
            <div class="field"><label for="ticket_status">Status</label><select id="ticket_status" name="status_ticket"><option value="ABERTO">ABERTO</option><option value="EM_ATENDIMENTO">EM_ATENDIMENTO</option><option value="RESOLVIDO">RESOLVIDO</option><option value="FECHADO">FECHADO</option></select></div>
            <button type="submit" <?= empty($capabilities['tickets']) ? 'disabled' : '' ?>>Abrir ticket</button>
        </form>
    </article>

    <article class="card">
        <h2>Assinatura digital (registro)</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/assinaturas-digitais')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_signature') ?>
            <div class="field"><label for="sig_conta_id">Conta</label><select id="sig_conta_id" name="conta_id" required><option value="">Selecione</option><?php foreach (($options['contas'] ?? []) as $conta): ?><option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="sig_tipo">Entidade tipo</label><input id="sig_tipo" name="entidade_tipo" type="text" placeholder="incidentes" required></div>
            <div class="field"><label for="sig_id">Entidade id</label><input id="sig_id" name="entidade_id" type="number" min="1" required></div>
            <div class="field"><label for="sig_hash">Hash SHA-256</label><input id="sig_hash" name="hash_documento" type="text" maxlength="64" required></div>
            <div class="field"><label for="sig_cert">Certificado ref</label><input id="sig_cert" name="certificado_ref" type="text"></div>
            <div class="field"><label for="sig_payload">Payload assinatura (JSON)</label><textarea id="sig_payload" name="assinatura_payload_json" rows="2" placeholder='{"provedor":"ICP"}'></textarea></div>
            <button type="submit" <?= empty($capabilities['assinatura_digital']) ? 'disabled' : '' ?>>Registrar assinatura</button>
        </form>
    </article>

    <article class="card">
        <h2>Relatorio executivo consolidado</h2>
        <form method="post" action="<?= e(url('/admin/enterprise/relatorios-executivos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_enterprise_exec_report') ?>
            <div class="field"><label for="rel_conta_id">Conta</label><select id="rel_conta_id" name="conta_id" required><option value="">Selecione</option><?php foreach (($options['contas'] ?? []) as $conta): ?><option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="rel_orgao_id">Orgao (opcional)</label><select id="rel_orgao_id" name="orgao_id"><option value="">Todos</option><?php foreach (($options['orgaos'] ?? []) as $orgao): ?><option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="rel_unidade_id">Unidade (opcional)</label><select id="rel_unidade_id" name="unidade_id"><option value="">Todas</option><?php foreach (($options['unidades'] ?? []) as $unidade): ?><option value="<?= e((string) $unidade['id']) ?>"><?= e((string) $unidade['nome_unidade']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="rel_inicio">Periodo inicio</label><input id="rel_inicio" name="periodo_inicio" type="date"></div>
            <div class="field"><label for="rel_fim">Periodo fim</label><input id="rel_fim" name="periodo_fim" type="date"></div>
            <button type="submit" <?= empty($capabilities['analytics']) ? 'disabled' : '' ?>>Gerar consolidado</button>
        </form>
    </article>
</section>
<section class="card table-card">
    <h2>Features e Apps API</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tipo</th><th>Conta</th><th>Orgao/Unidade</th><th>Codigo/Nome</th><th>Status</th><th>Atualizado</th></tr></thead>
            <tbody>
            <?php foreach ($featureFlags as $row): ?>
                <tr>
                    <td>FEATURE</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) ($row['orgao_nome'] ?? '')) ?> / <?= e((string) ($row['nome_unidade'] ?? '')) ?></td>
                    <td><?= e((string) $row['feature_code']) ?></td>
                    <td><?= e((string) $row['status_feature']) ?></td>
                    <td><?= e((string) $row['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($apiApps as $row): ?>
                <tr>
                    <td>API_APP</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) ($row['orgao_nome'] ?? '')) ?> / <?= e((string) ($row['nome_unidade'] ?? '')) ?></td>
                    <td><?= e((string) $row['nome_app']) ?> (<?= e((string) $row['token_prefix']) ?>)</td>
                    <td><?= e((string) $row['status_app']) ?></td>
                    <td><?= e((string) ($row['ultimo_uso_em'] ?? $row['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Integracoes e Automacoes</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tipo</th><th>Conta</th><th>Nome</th><th>Detalhe</th><th>Status</th><th>Criado</th></tr></thead>
            <tbody>
            <?php foreach ($integracoes as $row): ?>
                <tr>
                    <td>INTEGRACAO</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['nome_integracao']) ?></td>
                    <td><?= e((string) $row['tipo_integracao']) ?> - <?= e((string) $row['endpoint_url']) ?></td>
                    <td><?= e((string) $row['status_integracao']) ?></td>
                    <td><?= e((string) $row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($automacoes as $row): ?>
                <tr>
                    <td>AUTOMACAO</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['nome_regra']) ?></td>
                    <td><?= e((string) $row['evento_codigo']) ?> - <?= e((string) $row['acao_tipo']) ?></td>
                    <td><?= e((string) $row['status_regra']) ?></td>
                    <td><?= e((string) $row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>SLA e Tickets</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tipo</th><th>Conta</th><th>Codigo/Titulo</th><th>Prioridade</th><th>Status</th><th>Prazos</th></tr></thead>
            <tbody>
            <?php foreach ($slaPolicies as $row): ?>
                <tr>
                    <td>SLA</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['codigo_sla']) ?> - <?= e((string) $row['nome_sla']) ?></td>
                    <td><?= e((string) $row['prioridade']) ?></td>
                    <td><?= e((string) $row['status_sla']) ?></td>
                    <td>R: <?= e((string) $row['tempo_resposta_min']) ?>m / S: <?= e((string) $row['tempo_resolucao_min']) ?>m</td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($tickets as $row): ?>
                <tr>
                    <td>TICKET</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['titulo_ticket']) ?></td>
                    <td><?= e((string) $row['prioridade']) ?></td>
                    <td><?= e((string) $row['status_ticket']) ?></td>
                    <td><?= e((string) ($row['resposta_limite_em'] ?? '')) ?> / <?= e((string) ($row['resolucao_limite_em'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Assinaturas Digitais e Relatorios Executivos</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Tipo</th><th>Conta</th><th>Referencia</th><th>Indicadores</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($digitalSignatures as $row): ?>
                <tr>
                    <td>ASSINATURA</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['entidade_tipo']) ?> #<?= e((string) $row['entidade_id']) ?></td>
                    <td><?= e((string) $row['hash_documento']) ?></td>
                    <td><?= e((string) $row['assinado_em']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($executiveReports as $row): ?>
                <tr>
                    <td>REL_EXEC</td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) ($row['periodo_inicio'] ?? '')) ?> ate <?= e((string) ($row['periodo_fim'] ?? '')) ?></td>
                    <td>INC <?= e((string) $row['total_incidentes']) ?> | PLAN <?= e((string) $row['total_plancons']) ?> | SLA vencido <?= e((string) $row['total_tickets_sla_vencido']) ?></td>
                    <td><?= e((string) $row['gerado_em']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
