<?php

declare(strict_types=1);

$accounts = $accounts ?? [];
$orgaos = $orgaos ?? [];
$unidades = $unidades ?? [];
$usuarios = $usuarios ?? [];
$perfis = $perfis ?? [];
$vinculos = $vinculos ?? [];
$options = $options ?? [];
?>
<section class="hero">
    <h1>Gestao Institucional</h1>
    <p>Cadastro de contas, orgaos, unidades, usuarios, perfis e vinculos usuario-perfil.</p>
</section>

<section class="grid grid-2">
    <article class="card">
        <h2>Nova conta contratante</h2>
        <form method="post" action="<?= e(url('/admin/institucional/contas')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_conta_create') ?>
            <div class="field">
                <label for="conta_nome_fantasia">Nome fantasia</label>
                <input id="conta_nome_fantasia" name="nome_fantasia" type="text" required>
            </div>
            <div class="field">
                <label for="conta_razao_social">Razao social</label>
                <input id="conta_razao_social" name="razao_social" type="text">
            </div>
            <div class="field">
                <label for="conta_cpf_cnpj">CPF/CNPJ</label>
                <input id="conta_cpf_cnpj" name="cpf_cnpj" type="text">
            </div>
            <div class="field">
                <label for="conta_email_principal">Email principal</label>
                <input id="conta_email_principal" name="email_principal" type="email">
            </div>
            <div class="field">
                <label for="conta_status">Status</label>
                <select id="conta_status" name="status_cadastral">
                    <option value="ATIVA">ATIVA</option>
                    <option value="INATIVA">INATIVA</option>
                    <option value="BLOQUEADA">BLOQUEADA</option>
                </select>
            </div>
            <button type="submit">Salvar conta</button>
        </form>
    </article>

    <article class="card">
        <h2>Novo orgao</h2>
        <form method="post" action="<?= e(url('/admin/institucional/orgaos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_orgao_create') ?>
            <div class="field">
                <label for="orgao_conta_id">Conta</label>
                <select id="orgao_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>">
                            <?= e((string) $conta['nome_fantasia']) ?> (<?= e((string) $conta['status_cadastral']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="orgao_nome_oficial">Nome oficial</label>
                <input id="orgao_nome_oficial" name="nome_oficial" type="text" required>
            </div>
            <div class="field">
                <label for="orgao_sigla">Sigla</label>
                <input id="orgao_sigla" name="sigla" type="text">
            </div>
            <div class="field">
                <label for="orgao_cnpj">CNPJ</label>
                <input id="orgao_cnpj" name="cnpj" type="text">
            </div>
            <div class="field">
                <label for="orgao_status">Status</label>
                <select id="orgao_status" name="status_orgao">
                    <option value="ATIVO">ATIVO</option>
                    <option value="INATIVO">INATIVO</option>
                    <option value="BLOQUEADO">BLOQUEADO</option>
                </select>
            </div>
            <button type="submit">Salvar orgao</button>
        </form>
    </article>

    <article class="card">
        <h2>Nova unidade</h2>
        <form method="post" action="<?= e(url('/admin/institucional/unidades')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_unidade_create') ?>
            <div class="field">
                <label for="unidade_orgao_id">Orgao</label>
                <select id="unidade_orgao_id" name="orgao_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                        <option value="<?= e((string) $orgao['id']) ?>">
                            <?= e((string) $orgao['nome_oficial']) ?> (<?= e((string) $orgao['status_orgao']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="unidade_nome_unidade">Nome da unidade</label>
                <input id="unidade_nome_unidade" name="nome_unidade" type="text" required>
            </div>
            <div class="field">
                <label for="unidade_codigo_unidade">Codigo</label>
                <input id="unidade_codigo_unidade" name="codigo_unidade" type="text">
            </div>
            <div class="field">
                <label for="unidade_tipo_unidade">Tipo</label>
                <input id="unidade_tipo_unidade" name="tipo_unidade" type="text" placeholder="SEDE, REGIONAL, BASE">
            </div>
            <div class="field">
                <label for="unidade_status">Status</label>
                <select id="unidade_status" name="status_unidade">
                    <option value="ATIVA">ATIVA</option>
                    <option value="INATIVA">INATIVA</option>
                </select>
            </div>
            <button type="submit">Salvar unidade</button>
        </form>
    </article>

    <article class="card">
        <h2>Novo usuario</h2>
        <form method="post" action="<?= e(url('/admin/institucional/usuarios')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_usuario_create') ?>
            <div class="field">
                <label for="usuario_conta_id">Conta</label>
                <select id="usuario_conta_id" name="conta_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['contas'] ?? []) as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usuario_orgao_id">Orgao</label>
                <select id="usuario_orgao_id" name="orgao_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                        <option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usuario_unidade_id">Unidade</label>
                <select id="usuario_unidade_id" name="unidade_id">
                    <option value="">Sem unidade</option>
                    <?php foreach (($options['unidades'] ?? []) as $unidade): ?>
                        <option value="<?= e((string) $unidade['id']) ?>"><?= e((string) $unidade['nome_unidade']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usuario_nome_completo">Nome completo</label>
                <input id="usuario_nome_completo" name="nome_completo" type="text" required>
            </div>
            <div class="field">
                <label for="usuario_email_login">Email/login</label>
                <input id="usuario_email_login" name="email_login" type="email" required>
            </div>
            <div class="field">
                <label for="usuario_matricula_funcional">Matricula</label>
                <input id="usuario_matricula_funcional" name="matricula_funcional" type="text">
            </div>
            <div class="field">
                <label for="usuario_password">Senha inicial</label>
                <input id="usuario_password" name="password" type="password" required>
            </div>
            <div class="field">
                <label for="usuario_perfil_id">Perfil inicial (opcional)</label>
                <select id="usuario_perfil_id" name="perfil_id">
                    <option value="">Sem perfil inicial</option>
                    <?php foreach (($options['perfis'] ?? []) as $perfil): ?>
                        <option value="<?= e((string) $perfil['id']) ?>"><?= e((string) $perfil['nome_perfil']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="usuario_status">Status</label>
                <select id="usuario_status" name="status_usuario">
                    <option value="ATIVO">ATIVO</option>
                    <option value="INATIVO">INATIVO</option>
                    <option value="BLOQUEADO">BLOQUEADO</option>
                </select>
            </div>
            <button type="submit">Salvar usuario</button>
        </form>
    </article>

    <article class="card">
        <h2>Novo perfil</h2>
        <form method="post" action="<?= e(url('/admin/institucional/perfis')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_perfil_create') ?>
            <div class="field">
                <label for="perfil_nome_perfil">Nome do perfil</label>
                <input id="perfil_nome_perfil" name="nome_perfil" type="text" placeholder="GESTOR_REGIONAL" required>
            </div>
            <div class="field">
                <label for="perfil_descricao">Descricao</label>
                <input id="perfil_descricao" name="descricao" type="text">
            </div>
            <div class="field">
                <label for="perfil_status">Status</label>
                <select id="perfil_status" name="status_perfil">
                    <option value="ATIVO">ATIVO</option>
                    <option value="INATIVO">INATIVO</option>
                </select>
            </div>
            <button type="submit">Salvar perfil</button>
        </form>
    </article>

    <article class="card">
        <h2>Vincular usuario-perfil</h2>
        <form method="post" action="<?= e(url('/admin/institucional/vinculos')) ?>" data-guard-submit="true">
            <?= App\Support\Csrf::field('admin_usuario_perfil_bind') ?>
            <div class="field">
                <label for="vinculo_usuario_id">Usuario</label>
                <select id="vinculo_usuario_id" name="usuario_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['usuarios'] ?? []) as $usuario): ?>
                        <option value="<?= e((string) $usuario['id']) ?>">
                            <?= e((string) $usuario['nome_completo']) ?> (<?= e((string) $usuario['email_login']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="vinculo_perfil_id">Perfil</label>
                <select id="vinculo_perfil_id" name="perfil_id" required>
                    <option value="">Selecione</option>
                    <?php foreach (($options['perfis'] ?? []) as $perfil): ?>
                        <option value="<?= e((string) $perfil['id']) ?>"><?= e((string) $perfil['nome_perfil']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Salvar vinculo</button>
        </form>
    </article>
</section>

<section class="card table-card">
    <h2>Contas</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Status</th>
                <th>Email</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($accounts as $row): ?>
                <tr>
                    <td><?= e((string) $row['id']) ?></td>
                    <td><?= e((string) $row['nome_fantasia']) ?></td>
                    <td><?= e((string) $row['status_cadastral']) ?></td>
                    <td><?= e((string) ($row['email_principal'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Orgaos e unidades</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Orgao ID</th>
                <th>Conta</th>
                <th>Orgao</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orgaos as $row): ?>
                <tr>
                    <td><?= e((string) $row['id']) ?></td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['nome_oficial']) ?></td>
                    <td><?= e((string) $row['status_orgao']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap mt-1">
        <table>
            <thead>
            <tr>
                <th>Unidade ID</th>
                <th>Orgao</th>
                <th>Nome</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($unidades as $row): ?>
                <tr>
                    <td><?= e((string) $row['id']) ?></td>
                    <td><?= e((string) $row['orgao_nome']) ?></td>
                    <td><?= e((string) $row['nome_unidade']) ?></td>
                    <td><?= e((string) $row['status_unidade']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card table-card">
    <h2>Usuarios, perfis e vinculos</h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Login</th>
                <th>Conta</th>
                <th>Orgao</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $row): ?>
                <tr>
                    <td><?= e((string) $row['id']) ?></td>
                    <td><?= e((string) $row['nome_completo']) ?></td>
                    <td><?= e((string) $row['email_login']) ?></td>
                    <td><?= e((string) $row['conta_nome']) ?></td>
                    <td><?= e((string) $row['orgao_nome']) ?></td>
                    <td><?= e((string) $row['status_usuario']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap mt-1">
        <table>
            <thead>
            <tr>
                <th>Perfil ID</th>
                <th>Perfil</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($perfis as $row): ?>
                <tr>
                    <td><?= e((string) $row['id']) ?></td>
                    <td><?= e((string) $row['nome_perfil']) ?></td>
                    <td><?= e((string) $row['status_perfil']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrap mt-1">
        <table>
            <thead>
            <tr>
                <th>Usuario</th>
                <th>Perfil</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($vinculos as $row): ?>
                <tr>
                    <td><?= e((string) $row['usuario_nome']) ?></td>
                    <td><?= e((string) $row['nome_perfil']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
