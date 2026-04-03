-- SIGERD
-- Fase 0 - Fundacao tecnica
-- MySQL 8+ / MariaDB compativel

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS contas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_fantasia VARCHAR(150) NOT NULL,
    razao_social VARCHAR(180) NULL,
    cpf_cnpj VARCHAR(20) NULL,
    email_principal VARCHAR(160) NULL,
    status_cadastral ENUM('ATIVA', 'INATIVA', 'BLOQUEADA') NOT NULL DEFAULT 'ATIVA',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_contas_cpf_cnpj (cpf_cnpj),
    UNIQUE KEY uk_contas_email_principal (email_principal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orgaos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    nome_oficial VARCHAR(180) NOT NULL,
    sigla VARCHAR(30) NULL,
    cnpj VARCHAR(20) NULL,
    status_orgao ENUM('ATIVO', 'INATIVO', 'BLOQUEADO') NOT NULL DEFAULT 'ATIVO',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orgaos_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    UNIQUE KEY uk_orgaos_conta_nome (conta_id, nome_oficial),
    UNIQUE KEY uk_orgaos_cnpj (cnpj),
    KEY idx_orgaos_conta_status (conta_id, status_orgao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS unidades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orgao_id BIGINT UNSIGNED NOT NULL,
    unidade_superior_id BIGINT UNSIGNED NULL,
    codigo_unidade VARCHAR(40) NULL,
    nome_unidade VARCHAR(180) NOT NULL,
    tipo_unidade VARCHAR(80) NULL,
    status_unidade ENUM('ATIVA', 'INATIVA') NOT NULL DEFAULT 'ATIVA',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_unidades_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_unidades_superior FOREIGN KEY (unidade_superior_id) REFERENCES unidades(id),
    UNIQUE KEY uk_unidades_orgao_codigo (orgao_id, codigo_unidade),
    UNIQUE KEY uk_unidades_orgao_nome (orgao_id, nome_unidade),
    KEY idx_unidades_orgao_status (orgao_id, status_unidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS perfis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome_perfil VARCHAR(60) NOT NULL,
    descricao VARCHAR(255) NULL,
    status_perfil ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_perfis_nome (nome_perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_permissao VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_permissoes_codigo (codigo_permissao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    orgao_id BIGINT UNSIGNED NOT NULL,
    unidade_id BIGINT UNSIGNED NULL,
    nome_completo VARCHAR(180) NOT NULL,
    email_login VARCHAR(160) NOT NULL,
    matricula_funcional VARCHAR(80) NULL,
    password_hash VARCHAR(255) NOT NULL,
    status_usuario ENUM('ATIVO', 'INATIVO', 'BLOQUEADO') NOT NULL DEFAULT 'ATIVO',
    ultimo_acesso_em DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_usuarios_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_usuarios_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    UNIQUE KEY uk_usuarios_email_login (email_login),
    UNIQUE KEY uk_usuarios_orgao_matricula (orgao_id, matricula_funcional),
    KEY idx_usuarios_conta_orgao_status (conta_id, orgao_id, status_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios_perfis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    perfil_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_perfis_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_usuarios_perfis_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    UNIQUE KEY uk_usuarios_perfis (usuario_id, perfil_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS perfis_permissoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perfil_id BIGINT UNSIGNED NOT NULL,
    permissao_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfis_permissoes_perfil FOREIGN KEY (perfil_id) REFERENCES perfis(id),
    CONSTRAINT fk_perfis_permissoes_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes(id),
    UNIQUE KEY uk_perfis_permissoes (perfil_id, permissao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modulos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_modulo VARCHAR(80) NOT NULL,
    nome_modulo VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    status_modulo ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_modulos_codigo (codigo_modulo),
    UNIQUE KEY uk_modulos_nome (nome_modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessoes_usuario (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    session_id_hash CHAR(64) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    iniciada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expira_em DATETIME NULL,
    encerrada_em DATETIME NULL,
    ultimo_acesso_em DATETIME NULL,
    status_sessao ENUM('ATIVA', 'ENCERRADA', 'EXPIRADA', 'INVALIDADA') NOT NULL DEFAULT 'ATIVA',
    CONSTRAINT fk_sessoes_usuario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    KEY idx_sessoes_usuario_status (usuario_id, status_sessao),
    KEY idx_sessoes_usuario_ultimo_acesso (ultimo_acesso_em),
    UNIQUE KEY uk_sessoes_usuario_hash (session_id_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NULL,
    orgao_id BIGINT UNSIGNED NULL,
    unidade_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NULL,
    modulo_codigo VARCHAR(80) NOT NULL,
    acao VARCHAR(120) NOT NULL,
    resultado ENUM('SUCESSO', 'FALHA', 'NEGADO') NOT NULL DEFAULT 'SUCESSO',
    entidade_tipo VARCHAR(80) NULL,
    entidade_id BIGINT UNSIGNED NULL,
    detalhes_json JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_auditoria_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_logs_auditoria_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_logs_auditoria_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_logs_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    KEY idx_logs_auditoria_escopo (conta_id, orgao_id, unidade_id, usuario_id),
    KEY idx_logs_auditoria_evento (modulo_codigo, acao, resultado),
    KEY idx_logs_auditoria_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logs_acesso (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    conta_id BIGINT UNSIGNED NULL,
    orgao_id BIGINT UNSIGNED NULL,
    evento VARCHAR(80) NOT NULL,
    resultado ENUM('SUCESSO', 'FALHA') NOT NULL,
    motivo VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_acesso_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_logs_acesso_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_logs_acesso_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    KEY idx_logs_acesso_usuario_evento (usuario_id, evento, created_at),
    KEY idx_logs_acesso_conta_data (conta_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anexos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NULL,
    orgao_id BIGINT UNSIGNED NULL,
    usuario_envio_id BIGINT UNSIGNED NULL,
    entidade_tipo VARCHAR(80) NOT NULL,
    entidade_id BIGINT UNSIGNED NOT NULL,
    arquivo_nome VARCHAR(180) NOT NULL,
    arquivo_caminho VARCHAR(255) NOT NULL,
    arquivo_mime VARCHAR(120) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL,
    hash_arquivo CHAR(64) NULL,
    visibilidade ENUM('PRIVADO', 'INSTITUCIONAL', 'PUBLICO') NOT NULL DEFAULT 'PRIVADO',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_anexos_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_anexos_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_anexos_usuario_envio FOREIGN KEY (usuario_envio_id) REFERENCES usuarios(id),
    KEY idx_anexos_entidade (entidade_tipo, entidade_id),
    KEY idx_anexos_escopo (conta_id, orgao_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

