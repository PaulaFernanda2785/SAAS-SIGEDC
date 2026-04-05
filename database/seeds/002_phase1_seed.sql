-- SIGERD
-- Seed inicial fase 1 - Nucleo SaaS

START TRANSACTION;

INSERT INTO planos_catalogo
    (codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios, status_plano)
VALUES
    ('START', 'Plano Start', 'Plano inicial para operacao institucional em pequena escala', 149.90, 25, 'ATIVO'),
    ('PRO', 'Plano Pro', 'Plano para operacao multiunidade com maior capacidade', 329.90, 120, 'ATIVO'),
    ('ENTERPRISE', 'Plano Enterprise', 'Plano com operacao ampliada, governanca e escala', 649.90, NULL, 'ATIVO')
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    preco_mensal = VALUES(preco_mensal),
    limite_usuarios = VALUES(limite_usuarios),
    status_plano = VALUES(status_plano);

INSERT INTO assinaturas
    (conta_id, plano_id, status_assinatura, inicia_em, expira_em, trial_fim_em, motivo_status)
SELECT
    1,
    p.id,
    'ATIVA',
    CURDATE(),
    DATE_ADD(CURDATE(), INTERVAL 365 DAY),
    NULL,
    'assinatura_demo_fase_1'
FROM planos_catalogo p
WHERE p.codigo_plano = 'PRO'
  AND NOT EXISTS (
      SELECT 1
      FROM assinaturas a
      WHERE a.conta_id = 1
        AND a.status_assinatura IN ('TRIAL', 'ATIVA')
      LIMIT 1
  );

INSERT INTO assinaturas_modulos (assinatura_id, modulo_id, status_liberacao, liberado_em)
SELECT
    a.id,
    m.id,
    'ATIVA',
    NOW()
FROM assinaturas a
INNER JOIN modulos m ON m.codigo_modulo IN ('PUBLIC', 'AUTH', 'ADMIN', 'OPERATIONAL', 'AUDIT')
WHERE a.conta_id = 1
  AND a.status_assinatura IN ('TRIAL', 'ATIVA')
  AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
  AND NOT EXISTS (
      SELECT 1
      FROM assinaturas_modulos am
      WHERE am.assinatura_id = a.id
        AND am.modulo_id = m.id
  );

COMMIT;
