-- SIGERD
-- Fase 3 complementar - padronizacao territorial por UF e municipios
-- MySQL 8+ / MariaDB compativel

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS territorios_ufs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_ibge SMALLINT UNSIGNED NOT NULL,
    sigla CHAR(2) NOT NULL,
    nome VARCHAR(80) NOT NULL,
    regiao_codigo TINYINT UNSIGNED NULL,
    regiao_nome VARCHAR(30) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_territorios_ufs_codigo (codigo_ibge),
    UNIQUE KEY uk_territorios_ufs_sigla (sigla),
    UNIQUE KEY uk_territorios_ufs_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS territorios_municipios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_ibge INT UNSIGNED NOT NULL,
    uf_sigla CHAR(2) NOT NULL,
    nome_municipio VARCHAR(120) NOT NULL,
    latitude DECIMAL(11,8) NULL,
    longitude DECIMAL(11,8) NULL,
    regiao_codigo TINYINT UNSIGNED NULL,
    regiao_nome VARCHAR(30) NULL,
    area_km2 DECIMAL(12,3) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_territorios_municipios_uf FOREIGN KEY (uf_sigla) REFERENCES territorios_ufs(sigla),
    UNIQUE KEY uk_territorios_municipios_codigo (codigo_ibge),
    KEY idx_territorios_municipios_uf_nome (uf_sigla, nome_municipio),
    KEY idx_territorios_municipios_regiao (regiao_codigo, regiao_nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @has_col = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'contas'
      AND column_name = 'uf_sigla'
);
SET @sql = IF(@has_col = 0, 'ALTER TABLE contas ADD COLUMN uf_sigla CHAR(2) NULL AFTER cpf_cnpj', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'orgaos'
      AND column_name = 'uf_sigla'
);
SET @sql = IF(@has_col = 0, 'ALTER TABLE orgaos ADD COLUMN uf_sigla CHAR(2) NULL AFTER cnpj', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'unidades'
      AND column_name = 'uf_sigla'
);
SET @sql = IF(@has_col = 0, 'ALTER TABLE unidades ADD COLUMN uf_sigla CHAR(2) NULL AFTER tipo_unidade', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'usuarios'
      AND column_name = 'uf_sigla'
);
SET @sql = IF(@has_col = 0, 'ALTER TABLE usuarios ADD COLUMN uf_sigla CHAR(2) NULL AFTER unidade_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_col = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'assinaturas'
      AND column_name = 'uf_sigla'
);
SET @sql = IF(@has_col = 0, 'ALTER TABLE assinaturas ADD COLUMN uf_sigla CHAR(2) NULL AFTER conta_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'contas'
      AND index_name = 'idx_contas_uf'
);
SET @sql = IF(@has_idx = 0, 'ALTER TABLE contas ADD KEY idx_contas_uf (uf_sigla)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'orgaos'
      AND index_name = 'idx_orgaos_uf'
);
SET @sql = IF(@has_idx = 0, 'ALTER TABLE orgaos ADD KEY idx_orgaos_uf (uf_sigla)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'unidades'
      AND index_name = 'idx_unidades_uf'
);
SET @sql = IF(@has_idx = 0, 'ALTER TABLE unidades ADD KEY idx_unidades_uf (uf_sigla)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'usuarios'
      AND index_name = 'idx_usuarios_uf'
);
SET @sql = IF(@has_idx = 0, 'ALTER TABLE usuarios ADD KEY idx_usuarios_uf (uf_sigla)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_idx = (
    SELECT COUNT(*)
    FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name = 'assinaturas'
      AND index_name = 'idx_assinaturas_uf'
);
SET @sql = IF(@has_idx = 0, 'ALTER TABLE assinaturas ADD KEY idx_assinaturas_uf (uf_sigla)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
