-- SIGERD
-- Seed inicial fase 4 - Inteligencia operacional, documentos e governanca

START TRANSACTION;

INSERT INTO modulos (codigo_modulo, nome_modulo, descricao, status_modulo)
VALUES
    ('INTELLIGENCE', 'Inteligencia Operacional', 'Modulo de analytics, mapa operacional e hotspots', 'ATIVO'),
    ('DOCUMENTS', 'Documentos Operacionais', 'Modulo de anexos, evidencias e rastreabilidade documental', 'ATIVO'),
    ('GOVERNANCE', 'Governanca Operacional', 'Modulo de auditoria reforcada, conformidade e termos', 'ATIVO'),
    ('ADV_REPORTS', 'Relatorios Avancados', 'Modulo de relatorios analiticos avancados', 'ATIVO')
ON DUPLICATE KEY UPDATE
    nome_modulo = VALUES(nome_modulo),
    descricao = VALUES(descricao),
    status_modulo = VALUES(status_modulo);

INSERT INTO assinaturas_modulos (assinatura_id, modulo_id, status_liberacao, liberado_em)
SELECT
    a.id,
    m.id,
    'ATIVA',
    NOW()
FROM assinaturas a
INNER JOIN modulos m ON m.codigo_modulo IN ('INTELLIGENCE', 'DOCUMENTS', 'GOVERNANCE', 'ADV_REPORTS')
WHERE a.conta_id = 1
  AND a.status_assinatura IN ('TRIAL', 'ATIVA')
  AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
  AND NOT EXISTS (
      SELECT 1
      FROM assinaturas_modulos am
      WHERE am.assinatura_id = a.id
        AND am.modulo_id = m.id
  );

INSERT INTO permissoes (codigo_permissao, descricao)
VALUES
    ('operational.intelligence.view', 'Permite visualizar inteligencia operacional'),
    ('operational.documents.view', 'Permite visualizar documentos operacionais'),
    ('operational.documents.download', 'Permite baixar documentos operacionais'),
    ('operational.documents.upload', 'Permite anexar documentos operacionais'),
    ('operational.governance.view', 'Permite visualizar governanca operacional'),
    ('operational.governance.term.accept', 'Permite registrar aceite de termo operacional'),
    ('operational.reports.advanced', 'Permite visualizar relatorios operacionais avancados')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN (
    'operational.intelligence.view',
    'operational.documents.view',
    'operational.documents.download',
    'operational.reports.advanced'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA', 'OPERADOR', 'LEITOR')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN (
    'operational.documents.upload'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA', 'OPERADOR')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN (
    'operational.governance.view',
    'operational.governance.term.accept'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO governanca_termos_aceite
    (conta_id, orgao_id, unidade_id, usuario_id, termo_codigo, versao_termo, aceito_em, origem_ip, user_agent, detalhes_json)
SELECT
    u.conta_id,
    u.orgao_id,
    u.unidade_id,
    u.id,
    'OPER_GOV_BASE',
    '2026.04',
    NOW(),
    '127.0.0.1',
    'seed-phase4',
    JSON_OBJECT('origem', 'seed', 'observacao', 'aceite inicial de referencia fase 4')
FROM usuarios u
WHERE u.email_login = 'admin@sigerd.local'
  AND NOT EXISTS (
      SELECT 1
      FROM governanca_termos_aceite g
      WHERE g.usuario_id = u.id
        AND g.termo_codigo = 'OPER_GOV_BASE'
        AND g.versao_termo = '2026.04'
  )
LIMIT 1;

COMMIT;
