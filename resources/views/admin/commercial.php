<?php

declare(strict_types=1);

$plans = $plans ?? [];
$assinaturas = $assinaturas ?? [];
$modulos = $modulos ?? [];
$modulosLiberados = $modulosLiberados ?? [];
$contas = $contas ?? [];
$options = $options ?? [];
$currentUfFilter = $currentUfFilter ?? null;
$canSelectAllUf = $canSelectAllUf ?? false;
?>
<section class="hero">
    <h1>Gestao Comercial SaaS</h1>
    <p>Catalogo de planos, assinaturas por conta e liberacao de modulos contratados com padronizacao por UF.</p>
</section>

<section class="card">
    <h2>Filtro territorial</h2>
    <form method="get" action="<?= e(url('/admin/comercial')) ?>">
        <div class="field">
            <label for="filtro_comercial_uf">UF</label>
            <select
                id="filtro_comercial_uf"
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
        <h2>Novo plano</h2>
        <form method="post" action="<?= e(url('/admin/comercial/planos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_plano_create') ?>
            <div class="field">
                <label for="plano_codigo_plano">Codigo</label>
                <input id="plano_codigo_plano" name="codigo_plano" type="text" placeholder="START" required>
            </div>
            <div class="field">
                <label for="plano_nome_plano">Nome</label>
                <input id="plano_nome_plano" name="nome_plano" type="text" required>
            </div>
            <div class="field">
                <label for="plano_descricao">Descricao</label>
                <input id="plano_descricao" name="descricao" type="text">
            </div>
            <div class="field">
                <label for="plano_preco_mensal">Preco mensal (R$)</label>
                <input id="plano_preco_mensal" name="preco_mensal" type="text" placeholder="399.90" required>
            </div>
            <div class="field">
                <label for="plano_limite_usuarios">Limite de usuarios</label>
                <input id="plano_limite_usuarios" name="limite_usuarios" type="number" min="1">
            </div>
            <div class="field">
                <label for="plano_status">Status</label>
                <select id="plano_status" name="status_plano">
                    <option value="ATIVO">ATIVO</option>
                    <option value="INATIVO">INATIVO</option>
                </select>
            </div>
            <button type="submit">Salvar plano</button>
        </form>
    </article>

    <article class="card">
        <h2>Nova assinatura</h2>
        <form method="post" action="<?= e(url('/admin/comercial/assinaturas')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_assinatura_create') ?>
            <div class="field">
                <label for="assinatura_conta_id">Conta</label>
                <select id="assinatura_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>">
                            <?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?> (<?= e((string) $conta['status_cadastral']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="assinatura_plano_id">Plano</label>
                <select id="assinatura_plano_id" name="plano_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($plans as $plan): ?>
                        <option value="<?= e((string) $plan['id']) ?>"><?= e((string) $plan['nome_plano']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="assinatura_status_assinatura">Status</label>
                <select id="assinatura_status_assinatura" name="status_assinatura">
                    <option value="TRIAL">TRIAL</option>
                    <option value="ATIVA">ATIVA</option>
                    <option value="SUSPENSA">SUSPENSA</option>
                    <option value="CANCELADA">CANCELADA</option>
                    <option value="EXPIRADA">EXPIRADA</option>
                </select>
            </div>
            <div class="field">
                <label for="assinatura_inicia_em">Inicio</label>
                <input id="assinatura_inicia_em" name="inicia_em" type="date" required>
            </div>
            <div class="field">
                <label for="assinatura_expira_em">Expiracao</label>
                <input id="assinatura_expira_em" name="expira_em" type="date">
            </div>
            <div class="field">
                <label for="assinatura_trial_fim_em">Fim do trial</label>
                <input id="assinatura_trial_fim_em" name="trial_fim_em" type="date">
            </div>
            <div class="field">
                <label for="assinatura_motivo_status">Motivo/status</label>
                <input id="assinatura_motivo_status" name="motivo_status" type="text">
            </div>
            <div class="field">
                <label>Modulos liberados na criacao</label>
                <div class="checkbox-grid">
                    <?php foreach ($modulos as $modulo): ?>
                        <label class="checkbox-line">
                            <input type="checkbox" name="modulos[]" value="<?= e((string) $modulo['id']) ?>">
                            <?= e((string) $modulo['codigo_modulo']) ?> - <?= e((string) $modulo['nome_modulo']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit">Salvar assinatura</button>
        </form>
    </article>

    <article class="card">
        <h2>Atualizar liberacao de modulo</h2>
        <form method="post" action="<?= e(url('/admin/comercial/modulos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_assinatura_modulo_upsert') ?>
            <div class="field">
                <label for="liberacao_assinatura_id">Assinatura</label>
                <select id="liberacao_assinatura_id" name="assinatura_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($assinaturas as $assinatura): ?>
                        <option value="<?= e((string) $assinatura['id']) ?>">
                            #<?= e((string) $assinatura['id']) ?> - <?= e((string) $assinatura['conta_nome']) ?> (<?= e((string) $assinatura['status_assinatura']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="liberacao_modulo_id">Modulo</label>
                <select id="liberacao_modulo_id" name="modulo_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($modulos as $modulo): ?>
                        <option value="<?= e((string) $modulo['id']) ?>">
                            <?= e((string) $modulo['codigo_modulo']) ?> - <?= e((string) $modulo['nome_modulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="liberacao_status">Status da liberacao</label>
                <select id="liberacao_status" name="status_liberacao">
                    <option value="ATIVA">ATIVA</option>
                    <option value="BLOQUEADA">BLOQUEADA</option>
                </select>
            </div>
            <button type="submit">Atualizar modulo</button>
        </form>
    </article>
</section>

<section class="card table-card">
    <h2>Catalogo de planos</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Codigo</th>
                <th>Nome</th>
                <th>Mensal</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($plans as $plan): ?>
                <tr>
                    <td><?= e((string) $plan['id']) ?></td>
                    <td><?= e((string) $plan['codigo_plano']) ?></td>
                    <td><?= e((string) $plan['nome_plano']) ?></td>
                    <td>R$ <?= e(number_format((float) $plan['preco_mensal'], 2, ',', '.')) ?></td>
                    <td><?= e((string) $plan['status_plano']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Assinaturas</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Conta</th>
                <th>UF</th>
                <th>Plano</th>
                <th>Status</th>
                <th>Inicio</th>
                <th>Expira</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($assinaturas as $assinatura): ?>
                <tr>
                    <td><?= e((string) $assinatura['id']) ?></td>
                    <td><?= e((string) $assinatura['conta_nome']) ?></td>
                    <td><?= e((string) ($assinatura['uf_sigla'] ?? '')) ?></td>
                    <td><?= e((string) $assinatura['nome_plano']) ?></td>
                    <td><?= e((string) $assinatura['status_assinatura']) ?></td>
                    <td><?= e((string) $assinatura['inicia_em']) ?></td>
                    <td><?= e((string) ($assinatura['expira_em'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Modulos liberados por assinatura</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Assinatura</th>
                <th>UF</th>
                <th>Modulo</th>
                <th>Status</th>
                <th>Atualizado em</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($modulosLiberados as $row): ?>
                <tr>
                    <td>#<?= e((string) $row['assinatura_id']) ?></td>
                    <td><?= e((string) ($row['uf_sigla'] ?? '')) ?></td>
                    <td><?= e((string) $row['codigo_modulo']) ?> - <?= e((string) $row['nome_modulo']) ?></td>
                    <td><?= e((string) $row['status_liberacao']) ?></td>
                    <td><?= e((string) $row['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
