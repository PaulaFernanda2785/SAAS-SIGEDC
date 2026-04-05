-- SIGERD
-- Seed complementar fase 3 - UFs e vinculo territorial por assinatura/cadastros

START TRANSACTION;

INSERT INTO territorios_ufs (codigo_ibge, sigla, nome, regiao_codigo, regiao_nome)
VALUES
    (12, 'AC', 'Acre', 1, 'Norte'),
    (27, 'AL', 'Alagoas', 2, 'Nordeste'),
    (16, 'AP', 'Amapa', 1, 'Norte'),
    (13, 'AM', 'Amazonas', 1, 'Norte'),
    (29, 'BA', 'Bahia', 2, 'Nordeste'),
    (23, 'CE', 'Ceara', 2, 'Nordeste'),
    (53, 'DF', 'Distrito Federal', 5, 'Centro-Oeste'),
    (32, 'ES', 'Espirito Santo', 3, 'Sudeste'),
    (52, 'GO', 'Goias', 5, 'Centro-Oeste'),
    (21, 'MA', 'Maranhao', 2, 'Nordeste'),
    (51, 'MT', 'Mato Grosso', 5, 'Centro-Oeste'),
    (50, 'MS', 'Mato Grosso do Sul', 5, 'Centro-Oeste'),
    (31, 'MG', 'Minas Gerais', 3, 'Sudeste'),
    (15, 'PA', 'Para', 1, 'Norte'),
    (25, 'PB', 'Paraiba', 2, 'Nordeste'),
    (41, 'PR', 'Parana', 4, 'Sul'),
    (26, 'PE', 'Pernambuco', 2, 'Nordeste'),
    (22, 'PI', 'Piaui', 2, 'Nordeste'),
    (33, 'RJ', 'Rio de Janeiro', 3, 'Sudeste'),
    (24, 'RN', 'Rio Grande do Norte', 2, 'Nordeste'),
    (43, 'RS', 'Rio Grande do Sul', 4, 'Sul'),
    (11, 'RO', 'Rondonia', 1, 'Norte'),
    (14, 'RR', 'Roraima', 1, 'Norte'),
    (42, 'SC', 'Santa Catarina', 4, 'Sul'),
    (35, 'SP', 'Sao Paulo', 3, 'Sudeste'),
    (28, 'SE', 'Sergipe', 2, 'Nordeste'),
    (17, 'TO', 'Tocantins', 1, 'Norte')
ON DUPLICATE KEY UPDATE
    sigla = VALUES(sigla),
    nome = VALUES(nome),
    regiao_codigo = VALUES(regiao_codigo),
    regiao_nome = VALUES(regiao_nome);

UPDATE contas c
LEFT JOIN orgaos o ON o.conta_id = c.id
SET c.uf_sigla = COALESCE(c.uf_sigla, o.uf_sigla, 'TO')
WHERE c.uf_sigla IS NULL;

UPDATE orgaos o
INNER JOIN contas c ON c.id = o.conta_id
SET o.uf_sigla = COALESCE(o.uf_sigla, c.uf_sigla)
WHERE o.uf_sigla IS NULL;

UPDATE unidades u
INNER JOIN orgaos o ON o.id = u.orgao_id
SET u.uf_sigla = COALESCE(u.uf_sigla, o.uf_sigla)
WHERE u.uf_sigla IS NULL;

UPDATE usuarios u
INNER JOIN orgaos o ON o.id = u.orgao_id
INNER JOIN contas c ON c.id = u.conta_id
SET u.uf_sigla = COALESCE(u.uf_sigla, o.uf_sigla, c.uf_sigla)
WHERE u.uf_sigla IS NULL;

UPDATE assinaturas a
INNER JOIN contas c ON c.id = a.conta_id
SET a.uf_sigla = COALESCE(a.uf_sigla, c.uf_sigla)
WHERE a.uf_sigla IS NULL;

COMMIT;
