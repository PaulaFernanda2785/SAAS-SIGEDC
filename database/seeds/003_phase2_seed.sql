-- SIGERD
-- Seed inicial fase 2 - Nucleo operacional minimo viavel

START TRANSACTION;

INSERT INTO permissoes (codigo_permissao, descricao)
VALUES
    ('operational.incidents.view', 'Permite visualizar incidentes operacionais'),
    ('operational.incidents.create', 'Permite abrir incidente'),
    ('operational.briefing.create', 'Permite registrar briefing inicial'),
    ('operational.command.upsert', 'Permite registrar comando do incidente'),
    ('operational.periods.create', 'Permite abrir periodo operacional'),
    ('operational.records.create', 'Permite registrar diario operacional'),
    ('operational.reports.basic', 'Permite visualizar relatorios operacionais basicos')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN (
    'operational.incidents.view',
    'operational.reports.basic'
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
    'operational.incidents.create',
    'operational.briefing.create',
    'operational.command.upsert',
    'operational.periods.create',
    'operational.records.create'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA', 'OPERADOR')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO usuarios
    (conta_id, orgao_id, unidade_id, nome_completo, email_login, matricula_funcional, password_hash, status_usuario, created_at, updated_at)
SELECT
    1,
    1,
    1,
    'Coordenador Operacional SIGERD',
    'operador@sigerd.local',
    'OP-0002',
    '$2y$10$xFKd7tQgXTc3o/Wil/UJDOImup4jhSPAmQab7EnM89PpvQQnZ2kpG',
    'ATIVO',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1
    FROM usuarios
    WHERE email_login = 'operador@sigerd.local'
    LIMIT 1
);

INSERT INTO usuarios_perfis (usuario_id, perfil_id, created_at)
SELECT u.id, p.id, NOW()
FROM usuarios u
INNER JOIN perfis p ON p.nome_perfil = 'COORDENADOR'
WHERE u.email_login = 'operador@sigerd.local'
  AND NOT EXISTS (
      SELECT 1
      FROM usuarios_perfis up
      WHERE up.usuario_id = u.id
        AND up.perfil_id = p.id
  );

INSERT INTO usuarios_escopos (usuario_id, conta_id, orgao_id, escopo_acesso, status_escopo, created_at, updated_at)
SELECT u.id, u.conta_id, u.orgao_id, 'PROPRIO_ORGAO', 'ATIVO', NOW(), NOW()
FROM usuarios u
WHERE u.email_login = 'operador@sigerd.local'
  AND NOT EXISTS (
      SELECT 1
      FROM usuarios_escopos ue
      WHERE ue.usuario_id = u.id
        AND ue.escopo_acesso = 'PROPRIO_ORGAO'
  );

INSERT INTO incidentes
    (
        conta_id, orgao_id, unidade_id, numero_ocorrencia, nome_incidente, tipo_ocorrencia, classificacao_inicial,
        data_hora_acionamento, data_hora_abertura, municipio, local_detalhado, coordenadas,
        orgao_primeira_informacao, canal_recebimento, comunicante, descricao_inicial, situacao_inicial_observada,
        populacao_potencialmente_afetada, danos_humanos_iniciais, danos_materiais_iniciais, danos_ambientais_iniciais,
        riscos_imediatos, orgao_lider_inicial, status_incidente, plancon_id, cenario_id,
        aberto_por_usuario_id, created_at, updated_at
    )
SELECT
    u.conta_id,
    u.orgao_id,
    u.unidade_id,
    'INC-2026-0001',
    'Enxurrada em area urbana central',
    'INUNDACAO',
    'ALTA',
    NOW(),
    NOW(),
    'Municipio Demo',
    'Bairro Central, setor ribeirinho',
    '-10.1840,-48.3336',
    'Defesa Civil Municipal Demo',
    '193',
    'Central de monitoramento',
    'Transbordamento rapido com alagamento de vias e residencias.',
    'Fluxo de agua acima do esperado e bloqueio parcial de acesso.',
    1200,
    'Sem vitimas fatais no momento; 5 feridos leves.',
    'Danos em pontes locais e energia intermitente.',
    'Assoreamento de galeria e risco de contaminacao.',
    'Risco de novo transbordamento em 3 horas.',
    'Defesa Civil Municipal Demo',
    'ABERTO',
    NULL,
    NULL,
    u.id,
    NOW(),
    NOW()
FROM usuarios u
WHERE u.email_login = 'operador@sigerd.local'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes i
      WHERE i.orgao_id = u.orgao_id
        AND i.numero_ocorrencia = 'INC-2026-0001'
  )
LIMIT 1;

INSERT INTO incidentes_briefing
    (
        incidente_id, conta_id, orgao_id, unidade_id, versao_briefing, resumo_situacao, eventos_significativos,
        objetivos_iniciais, acoes_atuais, recursos_alocados, recursos_solicitados, riscos_criticos_seguranca,
        restricoes_operacionais, necessidades_imediatas, responsavel_briefing, data_hora_briefing,
        uso_transferencia_comando, observacoes, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    i.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    1,
    'Incidente em evolucao com pontos de inundacao em 4 quadras.',
    'Acionamento de equipes locais e apoio da guarda municipal.',
    'Salvar vidas, estabilizar acesso e proteger infraestrutura critica.',
    'Isolamento de area, retirada assistida e monitoramento hidrologico.',
    '2 viaturas, 1 bote, 12 agentes.',
    'Gerador movel, abrigo temporario e equipe de saude extra.',
    'Correnteza forte e risco eletrico em vias alagadas.',
    'Acesso limitado por ponte com trafego interrompido.',
    'Reforco logistico para alimentacao e agua.',
    'Coordenador Operacional SIGERD',
    NOW(),
    0,
    'Briefing inicial para alinhamento do primeiro periodo.',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_briefing b
      WHERE b.incidente_id = i.id
        AND b.versao_briefing = 1
  )
LIMIT 1;

INSERT INTO incidentes_comando
    (
        incidente_id, conta_id, orgao_id, unidade_id, tipo_comando, comandante_usuario_id, comandante_nome,
        instituicao_comandante, autoridade_legal, comando_unificado, data_hora_assuncao, data_hora_transferencia,
        motivo_transferencia, base_legal_ativacao, local_posto_comando, status_comando, diretrizes_institucionais,
        restricoes_juridicas_operacionais, observacoes, atualizado_por_usuario_id, created_at, updated_at
    )
SELECT
    i.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'UNICO',
    i.aberto_por_usuario_id,
    'Coordenador Operacional SIGERD',
    'Defesa Civil Municipal Demo',
    'Decreto municipal de resposta emergencial.',
    NULL,
    NOW(),
    NULL,
    NULL,
    'Ato interno de ativacao do posto de comando.',
    'Sede Operacional',
    'ATIVO',
    'Priorizar seguranca de equipes e retirada preventiva.',
    'Evitar operacao noturna em area sem iluminacao segura.',
    'Comando inicial definido para fase de estabilizacao.',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_comando c
      WHERE c.incidente_id = i.id
  )
LIMIT 1;

INSERT INTO incidentes_periodos_operacionais
    (
        incidente_id, conta_id, orgao_id, unidade_id, numero_periodo, data_hora_inicio, data_hora_fim,
        situacao_inicial_periodo, objetivos_periodo, recursos_principais_periodo, briefing_realizado, pai_vinculado,
        situacao_encerramento, pendencias, responsavel_aprovacao, status_periodo, registrado_por_usuario_id,
        created_at, updated_at
    )
SELECT
    i.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    1,
    NOW(),
    NULL,
    'Elevacao de nivel de agua com deslocamento populacional em curso.',
    'Concluir evacuacao das quadras de risco alto e manter rota de ambulancia.',
    'Viaturas de salvamento, bote e equipe de saude.',
    1,
    'PAI-INC-2026-0001-P1',
    NULL,
    'Confirmar disponibilidade de abrigo adicional.',
    'Coordenador Operacional SIGERD',
    'ATIVO',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_periodos_operacionais p
      WHERE p.incidente_id = i.id
        AND p.numero_periodo = 1
  )
LIMIT 1;

INSERT INTO incidentes_registros_operacionais
    (
        incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, data_hora_registro, tipo_registro,
        titulo_registro, descricao_registro, origem_informacao, responsavel_lancamento, encaminhamento, status_registro,
        criticidade, dados_json, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    i.id,
    p.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    NOW(),
    'ACIONAMENTO',
    'Acionamento inicial de equipes de campo',
    'Equipes deslocadas para pontos criticos de inundacao com prioridade para retirada de familias.',
    'Central de monitoramento',
    'Coordenador Operacional SIGERD',
    'Atualizar comando a cada 30 minutos com situacao das frentes.',
    'EM_ANDAMENTO',
    'ALTA',
    JSON_OBJECT('equipes', 3, 'viaturas', 2, 'botes', 1),
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
INNER JOIN incidentes_periodos_operacionais p
    ON p.incidente_id = i.id
   AND p.numero_periodo = 1
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_registros_operacionais r
      WHERE r.incidente_id = i.id
        AND r.titulo_registro = 'Acionamento inicial de equipes de campo'
  )
LIMIT 1;

COMMIT;
