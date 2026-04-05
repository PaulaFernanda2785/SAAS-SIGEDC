-- SIGERD
-- Fase 5 complementar - onboarding publico, faturamento e pagamentos de assinatura
-- MySQL 8+ / MariaDB compativel

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS assinaturas_faturas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    assinatura_id BIGINT UNSIGNED NOT NULL,
    plano_id BIGINT UNSIGNED NOT NULL,
    uf_sigla CHAR(2) NULL,
    ciclo_cobranca ENUM('MENSAL', 'ANUAL') NOT NULL DEFAULT 'MENSAL',
    moeda CHAR(3) NOT NULL DEFAULT 'BRL',
    valor_bruto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    desconto_valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    valor_liquido DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status_fatura ENUM('ABERTA', 'PAGA', 'VENCIDA', 'CANCELADA') NOT NULL DEFAULT 'ABERTA',
    vence_em DATE NULL,
    paga_em DATETIME NULL,
    referencia_externa VARCHAR(120) NULL,
    observacao VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_faturas_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_assinaturas_faturas_assinatura FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id),
    CONSTRAINT fk_assinaturas_faturas_plano FOREIGN KEY (plano_id) REFERENCES planos_catalogo(id),
    KEY idx_assinaturas_faturas_scope (conta_id, assinatura_id, status_fatura),
    KEY idx_assinaturas_faturas_vencimento (status_fatura, vence_em),
    KEY idx_assinaturas_faturas_uf (uf_sigla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS assinaturas_pagamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conta_id BIGINT UNSIGNED NOT NULL,
    assinatura_id BIGINT UNSIGNED NOT NULL,
    fatura_id BIGINT UNSIGNED NOT NULL,
    uf_sigla CHAR(2) NULL,
    gateway ENUM('MERCADO_PAGO') NOT NULL DEFAULT 'MERCADO_PAGO',
    status_pagamento ENUM('PENDENTE', 'APROVADO', 'RECUSADO', 'CANCELADO', 'ERRO') NOT NULL DEFAULT 'PENDENTE',
    checkout_token_prefix CHAR(12) NOT NULL,
    checkout_token_hash CHAR(64) NOT NULL,
    gateway_referencia VARCHAR(120) NULL,
    checkout_url VARCHAR(255) NULL,
    moeda CHAR(3) NOT NULL DEFAULT 'BRL',
    valor_liquido DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    response_excerpt VARCHAR(500) NULL,
    payload_json JSON NULL,
    processado_em DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_pagamentos_conta FOREIGN KEY (conta_id) REFERENCES contas(id),
    CONSTRAINT fk_assinaturas_pagamentos_assinatura FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id),
    CONSTRAINT fk_assinaturas_pagamentos_fatura FOREIGN KEY (fatura_id) REFERENCES assinaturas_faturas(id),
    UNIQUE KEY uk_assinaturas_pagamentos_token_hash (checkout_token_hash),
    KEY idx_assinaturas_pagamentos_scope (conta_id, assinatura_id, status_pagamento),
    KEY idx_assinaturas_pagamentos_gateway (gateway, status_pagamento),
    KEY idx_assinaturas_pagamentos_uf (uf_sigla),
    KEY idx_assinaturas_pagamentos_referencia (gateway_referencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
