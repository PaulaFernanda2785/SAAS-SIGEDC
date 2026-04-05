<?php

declare(strict_types=1);

namespace App\Repositories\SaaS;

use App\Support\Database;
use PDO;

final class BillingRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function billingSchemaReady(): bool
    {
        return $this->tableExists('assinaturas_faturas') && $this->tableExists('assinaturas_pagamentos');
    }

    public function createInvoice(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas_faturas
                (conta_id, assinatura_id, plano_id, uf_sigla, ciclo_cobranca, moeda,
                 valor_bruto, desconto_valor, valor_liquido, status_fatura, vence_em,
                 referencia_externa, observacao, created_at, updated_at)
             VALUES
                (:conta_id, :assinatura_id, :plano_id, :uf_sigla, :ciclo_cobranca, :moeda,
                 :valor_bruto, :desconto_valor, :valor_liquido, :status_fatura, :vence_em,
                 :referencia_externa, :observacao, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'assinatura_id' => $data['assinatura_id'],
            'plano_id' => $data['plano_id'],
            'uf_sigla' => $data['uf_sigla'] ?? null,
            'ciclo_cobranca' => $data['ciclo_cobranca'],
            'moeda' => $data['moeda'] ?? 'BRL',
            'valor_bruto' => $data['valor_bruto'],
            'desconto_valor' => $data['desconto_valor'] ?? 0,
            'valor_liquido' => $data['valor_liquido'],
            'status_fatura' => $data['status_fatura'] ?? 'ABERTA',
            'vence_em' => $data['vence_em'] ?? null,
            'referencia_externa' => $data['referencia_externa'] ?? null,
            'observacao' => $data['observacao'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createPayment(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas_pagamentos
                (conta_id, assinatura_id, fatura_id, uf_sigla, gateway, status_pagamento,
                 checkout_token_prefix, checkout_token_hash, gateway_referencia, checkout_url,
                 moeda, valor_liquido, response_excerpt, payload_json, processado_em,
                 created_at, updated_at)
             VALUES
                (:conta_id, :assinatura_id, :fatura_id, :uf_sigla, :gateway, :status_pagamento,
                 :checkout_token_prefix, :checkout_token_hash, :gateway_referencia, :checkout_url,
                 :moeda, :valor_liquido, :response_excerpt, :payload_json, :processado_em,
                 NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'assinatura_id' => $data['assinatura_id'],
            'fatura_id' => $data['fatura_id'],
            'uf_sigla' => $data['uf_sigla'] ?? null,
            'gateway' => $data['gateway'] ?? 'MERCADO_PAGO',
            'status_pagamento' => $data['status_pagamento'] ?? 'PENDENTE',
            'checkout_token_prefix' => $data['checkout_token_prefix'],
            'checkout_token_hash' => $data['checkout_token_hash'],
            'gateway_referencia' => $data['gateway_referencia'] ?? null,
            'checkout_url' => $data['checkout_url'] ?? null,
            'moeda' => $data['moeda'] ?? 'BRL',
            'valor_liquido' => $data['valor_liquido'],
            'response_excerpt' => $data['response_excerpt'] ?? null,
            'payload_json' => $this->jsonEncodeOrNull($data['payload'] ?? null),
            'processado_em' => $data['processado_em'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updatePaymentCheckoutMeta(int $paymentId, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE assinaturas_pagamentos
             SET gateway_referencia = :gateway_referencia,
                 checkout_url = :checkout_url,
                 response_excerpt = :response_excerpt,
                 payload_json = :payload_json,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'gateway_referencia' => $data['gateway_referencia'] ?? null,
            'checkout_url' => $data['checkout_url'] ?? null,
            'response_excerpt' => $data['response_excerpt'] ?? null,
            'payload_json' => $this->jsonEncodeOrNull($data['payload'] ?? null),
            'id' => $paymentId,
        ]);
    }

    public function paymentByCheckoutTokenHash(string $tokenHash): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ap.id AS pagamento_id, ap.conta_id, ap.assinatura_id, ap.fatura_id,
                    ap.uf_sigla, ap.gateway, ap.status_pagamento, ap.checkout_url,
                    ap.gateway_referencia, ap.moeda AS pagamento_moeda, ap.valor_liquido AS pagamento_valor,
                    JSON_UNQUOTE(JSON_EXTRACT(ap.payload_json, \'$.mode\')) AS checkout_mode,
                    af.status_fatura, af.ciclo_cobranca, af.valor_bruto, af.desconto_valor,
                    af.valor_liquido AS fatura_valor_liquido, af.moeda AS fatura_moeda, af.vence_em,
                    a.status_assinatura, a.inicia_em, a.expira_em, a.trial_fim_em,
                    a.plano_id, a.motivo_status,
                    p.codigo_plano, p.nome_plano,
                    u.id AS usuario_id, u.email_login, u.nome_completo
             FROM assinaturas_pagamentos ap
             INNER JOIN assinaturas_faturas af ON af.id = ap.fatura_id
             INNER JOIN assinaturas a ON a.id = ap.assinatura_id
             INNER JOIN planos_catalogo p ON p.id = a.plano_id
             LEFT JOIN usuarios u ON u.id = (
                SELECT u2.id
                FROM usuarios u2
                WHERE u2.conta_id = a.conta_id
                ORDER BY u2.id ASC
                LIMIT 1
             )
             WHERE ap.checkout_token_hash = :checkout_token_hash
             LIMIT 1'
        );
        $statement->execute([
            'checkout_token_hash' => $tokenHash,
        ]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function markPaymentStatus(int $paymentId, string $status, array $context = []): bool
    {
        $statement = $this->pdo()->prepare(
            'UPDATE assinaturas_pagamentos
             SET status_pagamento = :status_pagamento,
                 gateway_referencia = COALESCE(:gateway_referencia, gateway_referencia),
                 response_excerpt = :response_excerpt,
                 payload_json = :payload_json,
                 processado_em = :processado_em,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status_pagamento' => $status,
            'gateway_referencia' => $context['gateway_referencia'] ?? null,
            'response_excerpt' => $context['response_excerpt'] ?? null,
            'payload_json' => $this->jsonEncodeOrNull($context['payload'] ?? null),
            'processado_em' => $context['processado_em'] ?? date('Y-m-d H:i:s'),
            'id' => $paymentId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function markInvoicePaid(int $invoiceId, ?string $externalReference = null): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE assinaturas_faturas
             SET status_fatura = :status_fatura,
                 paga_em = NOW(),
                 referencia_externa = COALESCE(:referencia_externa, referencia_externa),
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status_fatura' => 'PAGA',
            'referencia_externa' => $externalReference,
            'id' => $invoiceId,
        ]);
    }

    public function markInvoiceStatus(int $invoiceId, string $status, ?string $observation = null): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE assinaturas_faturas
             SET status_fatura = :status_fatura,
                 observacao = :observacao,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status_fatura' => $status,
            'observacao' => $observation,
            'id' => $invoiceId,
        ]);
    }

    public function markAssinaturaAtiva(int $assinaturaId, string $iniciaEm, string $expiraEm): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE assinaturas
             SET status_assinatura = :status_assinatura,
                 inicia_em = :inicia_em,
                 expira_em = :expira_em,
                 trial_fim_em = NULL,
                 motivo_status = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status_assinatura' => 'ATIVA',
            'inicia_em' => $iniciaEm,
            'expira_em' => $expiraEm,
            'id' => $assinaturaId,
        ]);
    }

    public function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }

    private function tableExists(string $tableName): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name'
        );
        $statement->execute(['table_name' => $tableName]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    private function jsonEncodeOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? null : $json;
    }
}
