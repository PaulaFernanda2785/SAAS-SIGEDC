-- SIGERD
-- Seed complementar - Ajuste de precificacao promocional de lancamento

START TRANSACTION;

UPDATE planos_catalogo
SET preco_mensal = CASE UPPER(codigo_plano)
    WHEN 'START' THEN 149.90
    WHEN 'PRO' THEN 329.90
    WHEN 'ENTERPRISE' THEN 649.90
    ELSE preco_mensal
END,
updated_at = NOW()
WHERE UPPER(codigo_plano) IN ('START', 'PRO', 'ENTERPRISE');

COMMIT;

