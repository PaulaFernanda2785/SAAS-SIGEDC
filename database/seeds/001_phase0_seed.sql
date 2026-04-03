-- SIGERD
-- Seed inicial fase 0

START TRANSACTION;

INSERT INTO contas (id, nome_fantasia, razao_social, cpf_cnpj, email_principal, status_cadastral)
VALUES
    (1, 'Conta Demo SIGERD', 'Conta Demo SIGERD LTDA', '00000000000191', 'conta@sigerd.local', 'ATIVA')
ON DUPLICATE KEY UPDATE
    nome_fantasia = VALUES(nome_fantasia),
    razao_social = VALUES(razao_social),
    email_principal = VALUES(email_principal),
    status_cadastral = VALUES(status_cadastral);

INSERT INTO orgaos (id, conta_id, nome_oficial, sigla, cnpj, status_orgao)
VALUES
    (1, 1, 'Defesa Civil Municipal Demo', 'DCM', '00000000000192', 'ATIVO')
ON DUPLICATE KEY UPDATE
    nome_oficial = VALUES(nome_oficial),
    sigla = VALUES(sigla),
    status_orgao = VALUES(status_orgao);

INSERT INTO unidades (id, orgao_id, unidade_superior_id, codigo_unidade, nome_unidade, tipo_unidade, status_unidade)
VALUES
    (1, 1, NULL, 'SEDE', 'Sede Operacional', 'SEDE', 'ATIVA')
ON DUPLICATE KEY UPDATE
    nome_unidade = VALUES(nome_unidade),
    tipo_unidade = VALUES(tipo_unidade),
    status_unidade = VALUES(status_unidade);

INSERT INTO perfis (id, nome_perfil, descricao, status_perfil) VALUES
    (1, 'ADMIN_MASTER', 'Administrador master da plataforma', 'ATIVO'),
    (2, 'ADMIN_ORGAO', 'Administrador do orgao', 'ATIVO'),
    (3, 'GESTOR', 'Gestor institucional', 'ATIVO'),
    (4, 'COORDENADOR', 'Coordenador operacional', 'ATIVO'),
    (5, 'ANALISTA', 'Analista', 'ATIVO'),
    (6, 'OPERADOR', 'Operador', 'ATIVO'),
    (7, 'LEITOR', 'Leitura', 'ATIVO'),
    (8, 'FINANCEIRO', 'Financeiro', 'ATIVO'),
    (9, 'SUPORTE', 'Suporte tecnico', 'ATIVO'),
    (10, 'CONVIDADO', 'Convidado', 'ATIVO')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    status_perfil = VALUES(status_perfil);

INSERT INTO permissoes (id, codigo_permissao, descricao) VALUES
    (1, 'auth.login', 'Permite autenticar no sistema'),
    (2, 'auth.logout', 'Permite encerrar sessao'),
    (3, 'admin.dashboard.view', 'Permite acessar dashboard administrativo'),
    (4, 'operational.dashboard.view', 'Permite acessar dashboard operacional')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);

INSERT INTO perfis_permissoes (perfil_id, permissao_id) VALUES
    (1, 1), (1, 2), (1, 3), (1, 4),
    (2, 1), (2, 2), (2, 3),
    (3, 1), (3, 2), (3, 4),
    (4, 1), (4, 2), (4, 4),
    (5, 1), (5, 2), (5, 4),
    (6, 1), (6, 2), (6, 4),
    (7, 1), (7, 2), (7, 4),
    (8, 1), (8, 2), (8, 3),
    (9, 1), (9, 2), (9, 3),
    (10, 1), (10, 2)
ON DUPLICATE KEY UPDATE
    permissao_id = VALUES(permissao_id);

INSERT INTO modulos (id, codigo_modulo, nome_modulo, descricao, status_modulo) VALUES
    (1, 'PUBLIC', 'Area Publica', 'Modulo de paginas publicas', 'ATIVO'),
    (2, 'AUTH', 'Autenticacao', 'Modulo de identidade e sessao', 'ATIVO'),
    (3, 'ADMIN', 'Administracao SaaS', 'Modulo administrativo', 'ATIVO'),
    (4, 'OPERATIONAL', 'Operacional', 'Modulo operacional institucional', 'ATIVO'),
    (5, 'AUDIT', 'Auditoria', 'Trilha de auditoria funcional', 'ATIVO')
ON DUPLICATE KEY UPDATE
    nome_modulo = VALUES(nome_modulo),
    descricao = VALUES(descricao),
    status_modulo = VALUES(status_modulo);

INSERT INTO usuarios
    (id, conta_id, orgao_id, unidade_id, nome_completo, email_login, matricula_funcional, password_hash, status_usuario)
VALUES
    (1, 1, 1, 1, 'Administrador SIGERD', 'admin@sigerd.local', 'ADM-0001', '$2y$10$xFKd7tQgXTc3o/Wil/UJDOImup4jhSPAmQab7EnM89PpvQQnZ2kpG', 'ATIVO')
ON DUPLICATE KEY UPDATE
    nome_completo = VALUES(nome_completo),
    email_login = VALUES(email_login),
    matricula_funcional = VALUES(matricula_funcional),
    password_hash = VALUES(password_hash),
    status_usuario = VALUES(status_usuario);

INSERT INTO usuarios_perfis (usuario_id, perfil_id)
VALUES (1, 1)
ON DUPLICATE KEY UPDATE
    perfil_id = VALUES(perfil_id);

COMMIT;

