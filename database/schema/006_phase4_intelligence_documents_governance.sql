-- SIGERD
-- Fase 4 - Inteligencia operacional, documentos e governanca avancada
-- MySQL 8+ / MariaDB compativel

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS governanca_termos_aceite (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    orgao_id BIGINT UNSIGNED NOT NULL,
    unidade_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    termo_codigo VARCHAR(80) NOT NULL,
    versao_termo VARCHAR(30) NOT NULL,
    aceito_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    origem_ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    detalhes_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_governanca_termos_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_governanca_termos_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_governanca_termos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_governanca_termos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_governanca_termo_usuario_versao (usuario_id, termo_codigo, versao_termo),
    KEY idx_governanca_termos_escopo_data (conta_id, orgao_id, unidade_id, aceito_em),
    KEY idx_governanca_termos_codigo_versao (termo_codigo, versao_termo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relatorios_avancados_execucoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    orgao_id BIGINT UNSIGNED NOT NULL,
    unidade_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo_relatorio VARCHAR(60) NOT NULL,
    filtros_json JSON NULL,
    status_execucao ENUM('INICIADO', 'CONCLUIDO', 'FALHA') NOT NULL DEFAULT 'INICIADO',
    total_registros INT UNSIGNED NOT NULL DEFAULT 0,
    arquivo_caminho VARCHAR(255) NULL,
    gerado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_relatorios_execucoes_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_relatorios_execucoes_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_relatorios_execucoes_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_relatorios_execucoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    KEY idx_relatorios_execucoes_escopo_data (conta_id, orgao_id, unidade_id, gerado_em),
    KEY idx_relatorios_execucoes_tipo_status (tipo_relatorio, status_execucao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inteligencia_alertas_operacionais (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    orgao_id BIGINT UNSIGNED NOT NULL,
    unidade_id BIGINT UNSIGNED NULL,
    incidente_id BIGINT UNSIGNED NULL,
    alerta_codigo VARCHAR(80) NOT NULL,
    nivel_alerta ENUM('BAIXO', 'MODERADO', 'ALTO', 'CRITICO') NOT NULL DEFAULT 'MODERADO',
    mensagem_alerta VARCHAR(255) NOT NULL,
    status_alerta ENUM('ATIVO', 'MITIGADO', 'ENCERRADO') NOT NULL DEFAULT 'ATIVO',
    origem_geracao VARCHAR(80) NOT NULL DEFAULT 'REGRA_INTERNA',
    metadados_json JSON NULL,
    gerado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inteligencia_alertas_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_inteligencia_alertas_orgao FOREIGN KEY (orgao_id) REFERENCES orgaos(id),
    CONSTRAINT fk_inteligencia_alertas_unidade FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    CONSTRAINT fk_inteligencia_alertas_incidente FOREIGN KEY (incidente_id) REFERENCES incidentes(id),
    KEY idx_inteligencia_alertas_escopo_status (conta_id, orgao_id, unidade_id, status_alerta),
    KEY idx_inteligencia_alertas_incidente (incidente_id, nivel_alerta),
    KEY idx_inteligencia_alertas_data (gerado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
