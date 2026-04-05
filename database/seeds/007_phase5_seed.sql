-- SIGERD
-- Seed inicial fase 5 - Escala institucional, integracoes e recursos enterprise

START TRANSACTION;

INSERT INTO modulos (codigo_modulo, nome_modulo, descricao, status_modulo)
VALUES
    ('ENTERPRISE_CORE', 'Enterprise Core', 'Nucleo de governanca enterprise e multiinstitucional', 'ATIVO'),
    ('API_ENTERPRISE', 'API Enterprise', 'API controlada por chave e escopo institucional', 'ATIVO'),
    ('INTEGRACOES_EXTERNAS', 'Integracoes Externas', 'Conectores e webhooks institucionais', 'ATIVO'),
    ('AUTOMACOES', 'Automacoes', 'Regras automaticas de acao e notificacao', 'ATIVO'),
    ('ANALYTICS_EXECUTIVO', 'Analytics Executivo', 'Indicadores e relatorios consolidados de alta gestao', 'ATIVO'),
    ('SLA_SUPORTE', 'SLA e Suporte', 'Gestao de SLA e tickets de suporte institucional', 'ATIVO'),
    ('ASSINATURA_DIGITAL', 'Assinatura Digital', 'Registro de assinaturas digitais vinculadas a entidades', 'ATIVO')
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
INNER JOIN modulos m ON m.codigo_modulo IN (
    'ENTERPRISE_CORE',
    'API_ENTERPRISE',
    'INTEGRACOES_EXTERNAS',
    'AUTOMACOES',
    'ANALYTICS_EXECUTIVO',
    'SLA_SUPORTE',
    'ASSINATURA_DIGITAL'
)
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
    ('admin.enterprise.view', 'Permite acesso ao painel enterprise administrativo'),
    ('admin.enterprise.features.manage', 'Permite gerenciar feature flags enterprise'),
    ('admin.enterprise.api.manage', 'Permite gerenciar apps de API enterprise'),
    ('admin.enterprise.integrations.manage', 'Permite gerenciar integracoes externas'),
    ('admin.enterprise.automations.manage', 'Permite gerenciar automacoes enterprise'),
    ('admin.enterprise.analytics.view', 'Permite gerar relatorios executivos consolidados'),
    ('admin.enterprise.sla.manage', 'Permite gerenciar politicas SLA'),
    ('admin.enterprise.support.manage', 'Permite abrir e gerir tickets de suporte'),
    ('admin.enterprise.signature.register', 'Permite registrar assinatura digital institucional'),
    ('api.enterprise.executive.read', 'Permite leitura do resumo executivo via API controlada')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN (
    'admin.enterprise.view',
    'admin.enterprise.features.manage',
    'admin.enterprise.api.manage',
    'admin.enterprise.integrations.manage',
    'admin.enterprise.automations.manage',
    'admin.enterprise.analytics.view',
    'admin.enterprise.sla.manage',
    'admin.enterprise.support.manage',
    'admin.enterprise.signature.register'
)
WHERE p.nome_perfil IN ('ADMIN_MASTER', 'ADMIN_ORGAO')
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
    'admin.enterprise.view',
    'admin.enterprise.api.manage',
    'admin.enterprise.integrations.manage',
    'admin.enterprise.automations.manage',
    'admin.enterprise.analytics.view',
    'admin.enterprise.sla.manage',
    'admin.enterprise.support.manage',
    'admin.enterprise.signature.register'
)
WHERE p.nome_perfil IN ('SUPORTE')
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
    'admin.enterprise.view',
    'admin.enterprise.analytics.view',
    'admin.enterprise.sla.manage',
    'admin.enterprise.support.manage'
)
WHERE p.nome_perfil IN ('FINANCEIRO')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO enterprise_feature_flags
    (conta_id, orgao_id, unidade_id, feature_code, status_feature, plano_referencia, configuracoes_json, habilitado_por_usuario_id, created_at, updated_at)
SELECT
    1,
    1,
    NULL,
    'MULTI_UNIDADE_EXPANDIDA',
    'ATIVA',
    'ENTERPRISE',
    JSON_OBJECT('rollout', 100, 'seed', 'phase5'),
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM enterprise_feature_flags ef
    WHERE ef.conta_id = 1
      AND ef.orgao_id = 1
      AND ef.unidade_id IS NULL
      AND ef.feature_code = 'MULTI_UNIDADE_EXPANDIDA'
);

INSERT INTO sla_politicas
    (conta_id, orgao_id, unidade_id, codigo_sla, nome_sla, prioridade, tempo_resposta_min, tempo_resolucao_min, status_sla, criado_por_usuario_id, created_at, updated_at)
SELECT
    1,
    1,
    NULL,
    'SLA_CRITICO_4H',
    'SLA Critico 4h',
    'CRITICA',
    30,
    240,
    'ATIVA',
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM sla_politicas s
    WHERE s.conta_id = 1
      AND s.codigo_sla = 'SLA_CRITICO_4H'
);

INSERT INTO suporte_tickets
    (
        conta_id, orgao_id, unidade_id, sla_politica_id, titulo_ticket, descricao_ticket, prioridade, status_ticket,
        aberto_por_usuario_id, atribuido_para_usuario_id, aberto_em, resposta_limite_em, resolucao_limite_em,
        primeira_resposta_em, resolvido_em, created_at, updated_at
    )
SELECT
    1,
    1,
    1,
    s.id,
    'Ajuste de webhook institucional',
    'Validar entrega de eventos criticos para integracao homologada da conta demo.',
    'ALTA',
    'ABERTO',
    1,
    NULL,
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 MINUTE),
    DATE_ADD(NOW(), INTERVAL 4 HOUR),
    NULL,
    NULL,
    NOW(),
    NOW()
FROM sla_politicas s
WHERE s.conta_id = 1
  AND s.codigo_sla = 'SLA_CRITICO_4H'
  AND NOT EXISTS (
      SELECT 1
      FROM suporte_tickets t
      WHERE t.conta_id = 1
        AND t.titulo_ticket = 'Ajuste de webhook institucional'
  )
LIMIT 1;

INSERT INTO relatorios_executivos_consolidados
    (
        conta_id, orgao_id, unidade_id, periodo_inicio, periodo_fim, filtros_json, resumo_json,
        total_incidentes, total_plancons, total_alertas_ativos, total_tickets_abertos, total_tickets_sla_vencido,
        arquivo_caminho, gerado_por_usuario_id, gerado_em, created_at, updated_at
    )
SELECT
    1,
    1,
    NULL,
    DATE_SUB(CURDATE(), INTERVAL 30 DAY),
    CURDATE(),
    JSON_OBJECT('origem', 'seed_phase5', 'escopo', 'conta_1_orgao_1'),
    JSON_OBJECT('nota', 'consolidado inicial fase 5'),
    (SELECT COUNT(*) FROM incidentes i WHERE i.conta_id = 1 AND i.orgao_id = 1),
    (SELECT COUNT(*) FROM plancons p WHERE p.conta_id = 1 AND p.orgao_id = 1),
    (SELECT COUNT(*) FROM inteligencia_alertas_operacionais a WHERE a.conta_id = 1 AND a.orgao_id = 1 AND a.status_alerta = 'ATIVO'),
    (SELECT COUNT(*) FROM suporte_tickets t WHERE t.conta_id = 1 AND t.orgao_id = 1 AND t.status_ticket IN ('ABERTO', 'EM_ATENDIMENTO')),
    (SELECT COUNT(*) FROM suporte_tickets t WHERE t.conta_id = 1 AND t.orgao_id = 1 AND t.status_ticket IN ('ABERTO', 'EM_ATENDIMENTO')
        AND ((t.resposta_limite_em IS NOT NULL AND t.primeira_resposta_em IS NULL AND t.resposta_limite_em < NOW())
        OR (t.resolucao_limite_em IS NOT NULL AND t.resolvido_em IS NULL AND t.resolucao_limite_em < NOW()))),
    NULL,
    1,
    NOW(),
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM relatorios_executivos_consolidados r
    WHERE r.conta_id = 1
      AND r.orgao_id = 1
      AND r.periodo_inicio = DATE_SUB(CURDATE(), INTERVAL 30 DAY)
      AND r.periodo_fim = CURDATE()
);

COMMIT;
