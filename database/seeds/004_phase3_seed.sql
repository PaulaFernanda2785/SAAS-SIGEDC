-- SIGERD
-- Seed inicial fase 3 - Expansao do PLANCON e do gerenciamento de desastres

START TRANSACTION;

INSERT INTO modulos (codigo_modulo, nome_modulo, descricao, status_modulo)
VALUES
    ('PLANCON', 'PLANCON', 'Modulo de planos de contingencia e gestao de risco', 'ATIVO'),
    ('DISASTER_EXPANSION', 'Expansao de Desastres', 'Modulo de expansao operacional do SCI/SCO', 'ATIVO')
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
INNER JOIN modulos m ON m.codigo_modulo IN ('PLANCON', 'DISASTER_EXPANSION')
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
    ('plancon.view', 'Permite visualizar modulos PLANCON'),
    ('plancon.create', 'Permite criar plano de contingencia'),
    ('plancon.risk.create', 'Permite registrar risco no PLANCON'),
    ('plancon.scenario.create', 'Permite registrar cenario no PLANCON'),
    ('plancon.activation.create', 'Permite registrar nivel de ativacao no PLANCON'),
    ('plancon.resource.create', 'Permite registrar recurso no PLANCON'),
    ('plancon.review.create', 'Permite registrar revisao do PLANCON'),
    ('disaster.pai.create', 'Permite registrar estrategia PAI do incidente'),
    ('disaster.operations.create', 'Permite registrar operacoes de campo do incidente'),
    ('disaster.planning.create', 'Permite registrar planejamento situacional do incidente'),
    ('disaster.safety.create', 'Permite registrar seguranca operacional do incidente'),
    ('disaster.demobilization.create', 'Permite registrar desmobilizacao do incidente')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao);

INSERT INTO perfis_permissoes (perfil_id, permissao_id)
SELECT p.id, pe.id
FROM perfis p
INNER JOIN permissoes pe ON pe.codigo_permissao IN ('plancon.view')
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
    'plancon.create',
    'plancon.risk.create',
    'plancon.scenario.create',
    'plancon.activation.create',
    'plancon.resource.create',
    'plancon.review.create'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA')
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
    'disaster.pai.create',
    'disaster.operations.create',
    'disaster.planning.create',
    'disaster.safety.create',
    'disaster.demobilization.create'
)
WHERE p.nome_perfil IN ('GESTOR', 'COORDENADOR', 'ANALISTA', 'OPERADOR')
  AND NOT EXISTS (
      SELECT 1
      FROM perfis_permissoes pp
      WHERE pp.perfil_id = p.id
        AND pp.permissao_id = pe.id
  );

INSERT INTO plancons
    (
        conta_id, orgao_id, unidade_id, titulo_plano, municipio_estado, versao_documento, data_elaboracao, data_ultima_atualizacao,
        responsavel_tecnico, contato_institucional, vigencia_inicio, vigencia_fim, area_abrangencia, tipo_desastre_principal,
        outros_desastres_associados, base_legal_utilizada, objetivo_geral, objetivos_especificos, publico_alvo, status_plancon,
        observacoes_gerais, criado_por_usuario_id, atualizado_por_usuario_id, created_at, updated_at
    )
SELECT
    u.conta_id,
    u.orgao_id,
    u.unidade_id,
    'PLANCON Municipal de Resposta Hidrologica',
    'Municipio Demo - TO',
    'v1.0',
    CURDATE(),
    CURDATE(),
    'Coordenador Operacional SIGERD',
    'contato@defesacivil.demo',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 365 DAY),
    'Zona urbana central e bairros ribeirinhos',
    'INUNDACAO',
    'ALAGAMENTO, ENXURRADA',
    'Decreto municipal e normativos de defesa civil',
    'Reduzir impacto humano e estrutural em eventos hidrologicos.',
    'Padronizar ativacao, resposta, comunicacao e desmobilizacao.',
    'Populacao em areas suscetiveis e equipes de resposta.',
    'ATIVO',
    'Plano inicial da fase 3 para uso operacional institucional.',
    u.id,
    u.id,
    NOW(),
    NOW()
FROM usuarios u
WHERE u.email_login = 'operador@sigerd.local'
  AND NOT EXISTS (
      SELECT 1
      FROM plancons p
      WHERE p.orgao_id = u.orgao_id
        AND p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
        AND p.versao_documento = 'v1.0'
  )
LIMIT 1;

INSERT INTO plancon_riscos
    (
        plancon_id, conta_id, orgao_id, unidade_id, tipo_ameaca, descricao_risco, origem_risco, historico_ocorrencias,
        frequencia_ocorrencia, periodo_sazonal, areas_suscetiveis, populacao_exposta, infraestruturas_expostas, vulnerabilidades_identificadas,
        capacidade_local_resposta, probabilidade_evento, impacto_potencial, nivel_risco, fatores_agravantes, fatores_atenuantes,
        fontes_informacao_utilizadas, responsavel_analise, data_analise, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    p.id,
    p.conta_id,
    p.orgao_id,
    p.unidade_id,
    'HIDROLOGICA',
    'Transbordamento rapido de canais urbanos com inundacao de vias e residencias.',
    'Chuvas intensas e drenagem insuficiente.',
    'Registros sazonais nos ultimos 5 anos com picos no primeiro semestre.',
    'ALTA',
    'NOV-ABR',
    'Setores ribeirinhos e bairros baixos da area central.',
    '1200',
    'Pontes locais, escola municipal e unidade basica de saude.',
    'Ocupacao em faixa de inundacao e baixa redundancia de acesso.',
    'Equipes e recursos suficientes para resposta inicial de 24h.',
    'ALTA',
    'ALTO',
    'MUITO_ALTO',
    'Previsao de chuva concentrada e solo saturado.',
    'Sistema de alerta e rotas de evacuacao predefinidas.',
    'Historico municipal, monitoramento pluviometrico e vistorias locais.',
    'Coordenador Operacional SIGERD',
    CURDATE(),
    p.criado_por_usuario_id,
    NOW(),
    NOW()
FROM plancons p
WHERE p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
  AND NOT EXISTS (
      SELECT 1
      FROM plancon_riscos r
      WHERE r.plancon_id = p.id
        AND r.tipo_ameaca = 'HIDROLOGICA'
  )
LIMIT 1;

INSERT INTO plancon_cenarios
    (
        plancon_id, conta_id, orgao_id, unidade_id, nome_cenario, tipo_desastre_associado, descricao_cenario, evento_disparador,
        area_afetada_estimada, populacao_potencialmente_afetada, danos_humanos_esperados, danos_materiais_esperados,
        danos_ambientais_esperados, danos_sociais_esperados, servicos_interrompidos, tempo_evolucao_evento, necessidades_iniciais,
        prioridades_operacionais, classificacao_cenario, observacoes_cenario, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    p.id,
    p.conta_id,
    p.orgao_id,
    p.unidade_id,
    'Cenario 01 - Inundacao Urbana Rapida',
    'INUNDACAO',
    'Chuva extrema com elevacao rapida de nivel em canais urbanos.',
    'Acumulado pluviometrico superior a 80mm em 2 horas.',
    'Bairros Central, Ribeirinho e Norte.',
    '1500',
    'Feridos leves, deslocamento temporario de familias e risco a idosos.',
    'Danos em pavimento, equipamentos publicos e residencias.',
    'Assoreamento e contaminacao de galerias.',
    'Interrupcao parcial de servicos e deslocamento comunitario.',
    'Energia e mobilidade urbana com impacto parcial.',
    '2 a 6 horas',
    'Abrigo temporario, agua, saude e orientacao comunitaria.',
    'Salvar vidas, manter acessos criticos e estabilizar comunicacao.',
    'CRITICA',
    'Cenario de referencia para gatilhos de ativacao imediata.',
    p.criado_por_usuario_id,
    NOW(),
    NOW()
FROM plancons p
WHERE p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
  AND NOT EXISTS (
      SELECT 1
      FROM plancon_cenarios c
      WHERE c.plancon_id = p.id
        AND c.nome_cenario = 'Cenario 01 - Inundacao Urbana Rapida'
  )
LIMIT 1;

INSERT INTO plancon_niveis_ativacao
    (
        plancon_id, conta_id, orgao_id, unidade_id, nivel_operacional, criterios_ativacao, gatilhos_acionamento,
        autoridade_responsavel, acoes_automaticas, procedimentos_escalonamento, status_nivel, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    p.id,
    p.conta_id,
    p.orgao_id,
    p.unidade_id,
    'NIVEL 2 - ALERTA',
    'Risco alto confirmado em area urbana e previsao de chuva persistente.',
    'Cota hidrologica e pluviometria acima do limite de seguranca.',
    'Coordenacao Municipal de Defesa Civil',
    'Acionar equipes de campo, posto de comando e alerta comunitario.',
    'Escalonar para NIVEL 3 se houver agravamento em 60 minutos.',
    'ATIVO',
    p.criado_por_usuario_id,
    NOW(),
    NOW()
FROM plancons p
WHERE p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
  AND NOT EXISTS (
      SELECT 1
      FROM plancon_niveis_ativacao n
      WHERE n.plancon_id = p.id
        AND n.nivel_operacional = 'NIVEL 2 - ALERTA'
  )
LIMIT 1;

INSERT INTO plancon_recursos
    (
        plancon_id, conta_id, orgao_id, unidade_id, tipo_recurso, categoria_recurso, descricao_recurso, quantidade_disponivel,
        unidade_medida, localizacao_base, tempo_mobilizacao, status_recurso, responsavel_recurso, contato_responsavel, observacoes,
        registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    p.id,
    p.conta_id,
    p.orgao_id,
    p.unidade_id,
    'EQUIPAMENTO',
    'SALVAMENTO',
    'Botes inflaveis para resgate em area alagada.',
    3,
    'UN',
    'Sede Operacional',
    '20 min',
    'DISPONIVEL',
    'Coordenador Operacional SIGERD',
    '(63) 0000-0000',
    'Manter checklist de manutencao semanal.',
    p.criado_por_usuario_id,
    NOW(),
    NOW()
FROM plancons p
WHERE p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
  AND NOT EXISTS (
      SELECT 1
      FROM plancon_recursos r
      WHERE r.plancon_id = p.id
        AND r.tipo_recurso = 'EQUIPAMENTO'
        AND r.categoria_recurso = 'SALVAMENTO'
  )
LIMIT 1;

INSERT INTO plancon_revisoes
    (
        plancon_id, conta_id, orgao_id, unidade_id, versao_revisao, motivo_revisao, alteracoes_realizadas, pendencias,
        data_revisao, proxima_revisao, aprovado_por, status_revisao, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    p.id,
    p.conta_id,
    p.orgao_id,
    p.unidade_id,
    'REV-2026-01',
    'Primeira consolidacao operacional apos entrada da Fase 3.',
    'Inclusao de risco hidrologico, cenario critico, nivel de ativacao e recurso base.',
    'Validar simulacao interinstitucional em 60 dias.',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 180 DAY),
    'Defesa Civil Municipal Demo',
    'APROVADA',
    p.criado_por_usuario_id,
    NOW(),
    NOW()
FROM plancons p
WHERE p.titulo_plano = 'PLANCON Municipal de Resposta Hidrologica'
  AND NOT EXISTS (
      SELECT 1
      FROM plancon_revisoes r
      WHERE r.plancon_id = p.id
        AND r.versao_revisao = 'REV-2026-01'
  )
LIMIT 1;

INSERT INTO incidentes_estrategias_pai
    (
        incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, versao_pai, estrategia_geral, taticas_prioritarias,
        atividades_planejadas, responsavel_execucao, recursos_necessarios, areas_prioritarias, status_pai, registrado_por_usuario_id,
        created_at, updated_at
    )
SELECT
    i.id,
    p.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'PAI-INC-2026-0001-V1',
    'Estabilizar area critica e manter corredores de emergencia.',
    'Evacuacao assistida, isolamento de risco eletrico e monitoramento hidrologico continuo.',
    'Rotas de evacuacao, triagem de familias e apoio logistico de suprimentos.',
    'Coordenador Operacional SIGERD',
    'Viaturas, bote, equipe saude, apoio guarda municipal.',
    'Setor ribeirinho e acessos ao bairro central.',
    'APROVADO',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
LEFT JOIN incidentes_periodos_operacionais p ON p.incidente_id = i.id AND p.numero_periodo = 1
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_estrategias_pai x
      WHERE x.incidente_id = i.id
        AND x.versao_pai = 'PAI-INC-2026-0001-V1'
  )
LIMIT 1;

INSERT INTO incidentes_operacoes_campo
    (
        incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, frente_operacional, setor_operacional,
        supervisor_frente, missao_tatica, recursos_designados, situacao_atual, resultados_parciais, status_operacao,
        registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    i.id,
    p.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'Frente Alfa',
    'Setor Ribeirinho',
    'Coordenador Operacional SIGERD',
    'Retirada preventiva e controle de acesso em area alagada.',
    '2 viaturas, 1 bote, 8 agentes',
    'Operacao em andamento com fluxo estabilizado.',
    '32 familias retiradas com seguranca.',
    'ATIVA',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
LEFT JOIN incidentes_periodos_operacionais p ON p.incidente_id = i.id AND p.numero_periodo = 1
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_operacoes_campo o
      WHERE o.incidente_id = i.id
        AND o.frente_operacional = 'Frente Alfa'
  )
LIMIT 1;

INSERT INTO incidentes_planejamento_situacao
    (
        incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, situacao_consolidada, prognostico, cenario_provavel,
        pendencias_criticas, escalonamento_recomendado, status_planejamento, registrado_por_usuario_id, created_at, updated_at
    )
SELECT
    i.id,
    p.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'Incidente controlado parcialmente com equipes em campo e abrigo ativado.',
    'Persistencia de chuva moderada nas proximas 2 horas.',
    'Manter nivel de risco alto em setores baixos e reduzir apos 3 horas sem chuva forte.',
    'Garantir energia no abrigo e reforcar comunicacao comunitaria.',
    'Escalonar recursos logistico-hospitalares em caso de novo pico.',
    'VALIDADO',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
LEFT JOIN incidentes_periodos_operacionais p ON p.incidente_id = i.id AND p.numero_periodo = 1
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_planejamento_situacao s
      WHERE s.incidente_id = i.id
  )
LIMIT 1;

INSERT INTO incidentes_seguranca
    (
        incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, riscos_operacionais, equipes_expostas,
        medidas_controle, epis_recomendados, restricoes_operacionais, interdicoes, status_seguranca, registrado_por_usuario_id,
        created_at, updated_at
    )
SELECT
    i.id,
    p.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'Correnteza forte, risco de choque eletrico e piso instavel.',
    'Equipes de salvamento e apoio logistico de campo.',
    'Delimitar area, inspecionar energia e operar em duplas.',
    'Coletes, botas impermeaveis, capacete e luvas isolantes.',
    'Suspender deslocamento noturno em vias sem iluminacao segura.',
    'Interdicao parcial de ponte secundaria ate vistoria tecnica.',
    'ATIVA',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
LEFT JOIN incidentes_periodos_operacionais p ON p.incidente_id = i.id AND p.numero_periodo = 1
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_seguranca s
      WHERE s.incidente_id = i.id
  )
LIMIT 1;

INSERT INTO incidentes_desmobilizacao
    (
        incidente_id, conta_id, orgao_id, unidade_id, criterios_desmobilizacao, recursos_liberados, pendencias_finais,
        licoes_iniciais, situacao_final, data_hora_inicio, data_hora_encerramento, status_desmobilizacao, registrado_por_usuario_id,
        created_at, updated_at
    )
SELECT
    i.id,
    i.conta_id,
    i.orgao_id,
    i.unidade_id,
    'Iniciar quando nivel de agua normalizar e riscos eletricos forem eliminados.',
    'Liberacao progressiva de viaturas apos validacao de acessos.',
    'Revisar dano estrutural da ponte secundaria.',
    'Reforcar protocolo de alerta comunitario para chuva de curtissimo prazo.',
    'Desmobilizacao planejada para 24h apos estabilizacao total.',
    NOW(),
    NULL,
    'PLANEJADA',
    i.aberto_por_usuario_id,
    NOW(),
    NOW()
FROM incidentes i
WHERE i.numero_ocorrencia = 'INC-2026-0001'
  AND NOT EXISTS (
      SELECT 1
      FROM incidentes_desmobilizacao d
      WHERE d.incidente_id = i.id
  )
LIMIT 1;

COMMIT;
