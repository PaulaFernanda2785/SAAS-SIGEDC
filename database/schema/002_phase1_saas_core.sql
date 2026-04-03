-- SIGERD
-- Fase 1 - Nucleo SaaS e identidade institucional
-- MySQL 8+ / MariaDB compativel

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS planos_catalogo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_plano VARCHAR(60) NOT NULL,
    nome_plano VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    preco_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    limite_usuarios INT UNSIGNED NULL,
    status_plano ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_planos_catalogo_codigo (codigo_plano),
    UNIQUE KEY uk_planos_catalogo_nome (nome_plano),
    KEY idx_planos_catalogo_status (status_plano)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assinaturas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    plano_id BIGINT UNSIGNED NOT NULL,
    status_assinatura ENUM('TRIAL', 'ATIVA', 'SUSPENSA', 'CANCELADA', 'EXPIRADA') NOT NULL DEFAULT 'TRIAL',
    inicia_em DATE NOT NULL,
    expira_em DATE NULL,
    trial_fim_em DATE NULL,
    motivo_status VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_assinaturas_plano FOREIGN KEY (plano_id) REFERENCES planos_catalogo(id),
    KEY idx_assinaturas_conta_status (conta_id, status_assinatura),
    KEY idx_assinaturas_periodo (inicia_em, expira_em),
    KEY idx_assinaturas_plano (plano_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assinaturas_modulos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id BIGINT UNSIGNED NOT NULL,
    modulo_id BIGINT UNSIGNED NOT NULL,
    status_liberacao ENUM('ATIVA', 'BLOQUEADA') NOT NULL DEFAULT 'ATIVA',
    liberado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bloqueado_em DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_modulos_assinatura FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id),
    CONSTRAINT fk_assinaturas_modulos_modulo FOREIGN KEY (modulo_id) REFERENCES modulos(id),
    UNIQUE KEY uk_assinaturas_modulos (assinatura_id, modulo_id),
    KEY idx_assinaturas_modulos_status (status_liberacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    email_login VARCHAR(160) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expira_em DATETIME NOT NULL,
    consumido_em DATETIME NULL,
    solicitado_ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_password_resets_token (token_hash),
    KEY idx_password_resets_email (email_login),
    KEY idx_password_resets_expira (expira_em, consumido_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
