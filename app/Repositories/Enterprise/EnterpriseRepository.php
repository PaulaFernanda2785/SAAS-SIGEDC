<?php

declare(strict_types=1);

namespace App\Repositories\Enterprise;

use App\Support\Database;
use PDO;

final class EnterpriseRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function summary(?string $ufSigla = null): array
    {
        if (!$this->tableExists('enterprise_feature_flags')) {
            return [
                'features_ativas' => 0,
                'api_apps_ativas' => 0,
                'integracoes_ativas' => 0,
                'automacoes_ativas' => 0,
                'sla_ativas' => 0,
                'tickets_abertos' => 0,
                'tickets_sla_vencido' => 0,
                'assinaturas_digitais' => 0,
                'relatorios_executivos' => 0,
            ];
        }

        return [
            'features_ativas' => $this->countScoped('enterprise_feature_flags', "status_feature = 'ATIVA'", $ufSigla),
            'api_apps_ativas' => $this->countScoped('api_client_apps', "status_app = 'ATIVA'", $ufSigla),
            'integracoes_ativas' => $this->countScoped('integracoes_externas', "status_integracao = 'ATIVA'", $ufSigla),
            'automacoes_ativas' => $this->countScoped('automacoes_regras', "status_regra = 'ATIVA'", $ufSigla),
            'sla_ativas' => $this->countScoped('sla_politicas', "status_sla = 'ATIVA'", $ufSigla),
            'tickets_abertos' => $this->countScoped('suporte_tickets', "status_ticket IN ('ABERTO', 'EM_ATENDIMENTO')", $ufSigla),
            'tickets_sla_vencido' => $this->countScoped(
                'suporte_tickets',
                "status_ticket IN ('ABERTO', 'EM_ATENDIMENTO') AND ((resposta_limite_em IS NOT NULL AND primeira_resposta_em IS NULL AND resposta_limite_em < NOW()) OR (resolucao_limite_em IS NOT NULL AND resolvido_em IS NULL AND resolucao_limite_em < NOW()))",
                $ufSigla
            ),
            'assinaturas_digitais' => $this->countScoped('assinaturas_digitais_registros', null, $ufSigla),
            'relatorios_executivos' => $this->countScoped('relatorios_executivos_consolidados', null, $ufSigla),
        ];
    }

    public function featureFlags(?string $ufSigla = null, int $limit = 160): array
    {
        if (!$this->tableExists('enterprise_feature_flags')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('ef', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 160, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                ef.id,
                ef.conta_id,
                c.nome_fantasia AS conta_nome,
                ef.orgao_id,
                o.nome_oficial AS orgao_nome,
                ef.unidade_id,
                u.nome_unidade,
                ef.feature_code,
                ef.status_feature,
                ef.plano_referencia,
                ef.created_at,
                ef.updated_at
             FROM enterprise_feature_flags ef
             INNER JOIN contas c ON c.id = ef.conta_id
             LEFT JOIN orgaos o ON o.id = ef.orgao_id
             LEFT JOIN unidades u ON u.id = ef.unidade_id
             {$whereSql}
             ORDER BY ef.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createFeatureFlag(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO enterprise_feature_flags
                (
                    conta_id, orgao_id, unidade_id, feature_code, status_feature,
                    plano_referencia, configuracoes_json, habilitado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :feature_code, :status_feature,
                    :plano_referencia, :configuracoes_json, :habilitado_por_usuario_id, NOW(), NOW()
                )
             ON DUPLICATE KEY UPDATE
                status_feature = VALUES(status_feature),
                plano_referencia = VALUES(plano_referencia),
                configuracoes_json = VALUES(configuracoes_json),
                habilitado_por_usuario_id = VALUES(habilitado_por_usuario_id),
                updated_at = NOW()'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'feature_code' => $data['feature_code'],
            'status_feature' => $data['status_feature'] ?? 'ATIVA',
            'plano_referencia' => $data['plano_referencia'] ?? null,
            'configuracoes_json' => $this->jsonEncodeOrNull($data['configuracoes'] ?? null),
            'habilitado_por_usuario_id' => $data['habilitado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function apiClientApps(?string $ufSigla = null, int $limit = 160): array
    {
        if (!$this->tableExists('api_client_apps')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('a', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 160, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                a.id,
                a.conta_id,
                c.nome_fantasia AS conta_nome,
                a.orgao_id,
                o.nome_oficial AS orgao_nome,
                a.unidade_id,
                u.nome_unidade,
                a.nome_app,
                a.token_prefix,
                a.limite_rpm,
                a.status_app,
                a.ultimo_uso_em,
                a.expira_em,
                a.created_at
             FROM api_client_apps a
             INNER JOIN contas c ON c.id = a.conta_id
             LEFT JOIN orgaos o ON o.id = a.orgao_id
             LEFT JOIN unidades u ON u.id = a.unidade_id
             {$whereSql}
             ORDER BY a.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createApiClientApp(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO api_client_apps
                (
                    conta_id, orgao_id, unidade_id, nome_app, token_prefix, token_hash,
                    escopos_json, limite_rpm, status_app, ultimo_uso_em, expira_em,
                    criado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :nome_app, :token_prefix, :token_hash,
                    :escopos_json, :limite_rpm, :status_app, NULL, :expira_em,
                    :criado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_app' => $data['nome_app'],
            'token_prefix' => $data['token_prefix'],
            'token_hash' => $data['token_hash'],
            'escopos_json' => $this->jsonEncodeOrNull($data['escopos'] ?? null),
            'limite_rpm' => $data['limite_rpm'] ?? 600,
            'status_app' => $data['status_app'] ?? 'ATIVA',
            'expira_em' => $data['expira_em'] ?? null,
            'criado_por_usuario_id' => $data['criado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function apiClientByTokenHash(string $tokenHash): ?array
    {
        if (!$this->tableExists('api_client_apps')) {
            return null;
        }

        $statement = $this->pdo()->prepare(
            "SELECT
                a.id,
                a.conta_id,
                a.orgao_id,
                a.unidade_id,
                a.nome_app,
                a.token_prefix,
                a.token_hash,
                a.escopos_json,
                a.limite_rpm,
                a.status_app,
                a.expira_em,
                a.criado_por_usuario_id,
                c.uf_sigla
             FROM api_client_apps a
             INNER JOIN contas c ON c.id = a.conta_id
             WHERE a.token_hash = :token_hash
               AND a.status_app = 'ATIVA'
               AND (a.expira_em IS NULL OR a.expira_em >= NOW())
             LIMIT 1"
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        $row['escopos'] = $this->decodeJsonArray($row['escopos_json'] ?? null);

        return $row;
    }

    public function touchApiClientUsage(int $apiClientId): void
    {
        if (!$this->tableExists('api_client_apps')) {
            return;
        }

        $statement = $this->pdo()->prepare(
            'UPDATE api_client_apps
             SET ultimo_uso_em = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $apiClientId]);
    }

    public function hasActiveModuleByConta(int $contaId, string $moduleCode): bool
    {
        if (!$this->tableExists('assinaturas') || !$this->tableExists('assinaturas_modulos')) {
            return false;
        }

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM assinaturas a
             INNER JOIN assinaturas_modulos am ON am.assinatura_id = a.id
             INNER JOIN modulos m ON m.id = am.modulo_id
             WHERE a.conta_id = :conta_id
               AND a.status_assinatura IN ('TRIAL', 'ATIVA')
               AND a.inicia_em <= CURDATE()
               AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
               AND am.status_liberacao = 'ATIVA'
               AND m.codigo_modulo = :modulo_codigo"
        );
        $statement->execute([
            'conta_id' => $contaId,
            'modulo_codigo' => $moduleCode,
        ]);

        return ((int) $statement->fetchColumn()) > 0;
    }
    public function integracoes(?string $ufSigla = null, int $limit = 160): array
    {
        if (!$this->tableExists('integracoes_externas')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('i', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 160, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                i.id,
                i.conta_id,
                c.nome_fantasia AS conta_nome,
                i.orgao_id,
                o.nome_oficial AS orgao_nome,
                i.unidade_id,
                u.nome_unidade,
                i.nome_integracao,
                i.tipo_integracao,
                i.endpoint_url,
                i.auth_tipo,
                i.status_integracao,
                i.timeout_ms,
                i.created_at
             FROM integracoes_externas i
             INNER JOIN contas c ON c.id = i.conta_id
             LEFT JOIN orgaos o ON o.id = i.orgao_id
             LEFT JOIN unidades u ON u.id = i.unidade_id
             {$whereSql}
             ORDER BY i.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createIntegracao(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO integracoes_externas
                (
                    conta_id, orgao_id, unidade_id, nome_integracao, tipo_integracao, endpoint_url,
                    auth_tipo, credencial_ref, timeout_ms, status_integracao, configuracoes_json,
                    criado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :nome_integracao, :tipo_integracao, :endpoint_url,
                    :auth_tipo, :credencial_ref, :timeout_ms, :status_integracao, :configuracoes_json,
                    :criado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_integracao' => $data['nome_integracao'],
            'tipo_integracao' => $data['tipo_integracao'],
            'endpoint_url' => $data['endpoint_url'],
            'auth_tipo' => $data['auth_tipo'],
            'credencial_ref' => $data['credencial_ref'] ?? null,
            'timeout_ms' => $data['timeout_ms'] ?? 4000,
            'status_integracao' => $data['status_integracao'] ?? 'ATIVA',
            'configuracoes_json' => $this->jsonEncodeOrNull($data['configuracoes'] ?? null),
            'criado_por_usuario_id' => $data['criado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function automacoes(?string $ufSigla = null, int $limit = 160): array
    {
        if (!$this->tableExists('automacoes_regras')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('a', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 160, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                a.id,
                a.conta_id,
                c.nome_fantasia AS conta_nome,
                a.orgao_id,
                o.nome_oficial AS orgao_nome,
                a.unidade_id,
                u.nome_unidade,
                a.nome_regra,
                a.evento_codigo,
                a.acao_tipo,
                a.status_regra,
                a.created_at
             FROM automacoes_regras a
             INNER JOIN contas c ON c.id = a.conta_id
             LEFT JOIN orgaos o ON o.id = a.orgao_id
             LEFT JOIN unidades u ON u.id = a.unidade_id
             {$whereSql}
             ORDER BY a.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createAutomacao(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO automacoes_regras
                (
                    conta_id, orgao_id, unidade_id, nome_regra, evento_codigo,
                    condicao_json, acao_tipo, acao_config_json, status_regra,
                    criado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :nome_regra, :evento_codigo,
                    :condicao_json, :acao_tipo, :acao_config_json, :status_regra,
                    :criado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_regra' => $data['nome_regra'],
            'evento_codigo' => $data['evento_codigo'],
            'condicao_json' => $this->jsonEncodeOrNull($data['condicao'] ?? null),
            'acao_tipo' => $data['acao_tipo'],
            'acao_config_json' => $this->jsonEncodeOrNull($data['acao_config'] ?? null),
            'status_regra' => $data['status_regra'] ?? 'ATIVA',
            'criado_por_usuario_id' => $data['criado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function slaPolicies(?string $ufSigla = null, int $limit = 160): array
    {
        if (!$this->tableExists('sla_politicas')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('s', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 160, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                s.id,
                s.conta_id,
                c.nome_fantasia AS conta_nome,
                s.orgao_id,
                o.nome_oficial AS orgao_nome,
                s.unidade_id,
                u.nome_unidade,
                s.codigo_sla,
                s.nome_sla,
                s.prioridade,
                s.tempo_resposta_min,
                s.tempo_resolucao_min,
                s.status_sla,
                s.created_at
             FROM sla_politicas s
             INNER JOIN contas c ON c.id = s.conta_id
             LEFT JOIN orgaos o ON o.id = s.orgao_id
             LEFT JOIN unidades u ON u.id = s.unidade_id
             {$whereSql}
             ORDER BY s.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createSlaPolicy(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO sla_politicas
                (
                    conta_id, orgao_id, unidade_id, codigo_sla, nome_sla, prioridade,
                    tempo_resposta_min, tempo_resolucao_min, status_sla,
                    criado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :codigo_sla, :nome_sla, :prioridade,
                    :tempo_resposta_min, :tempo_resolucao_min, :status_sla,
                    :criado_por_usuario_id, NOW(), NOW()
                )
             ON DUPLICATE KEY UPDATE
                nome_sla = VALUES(nome_sla),
                prioridade = VALUES(prioridade),
                tempo_resposta_min = VALUES(tempo_resposta_min),
                tempo_resolucao_min = VALUES(tempo_resolucao_min),
                status_sla = VALUES(status_sla),
                criado_por_usuario_id = VALUES(criado_por_usuario_id),
                updated_at = NOW()'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'codigo_sla' => $data['codigo_sla'],
            'nome_sla' => $data['nome_sla'],
            'prioridade' => $data['prioridade'],
            'tempo_resposta_min' => $data['tempo_resposta_min'],
            'tempo_resolucao_min' => $data['tempo_resolucao_min'],
            'status_sla' => $data['status_sla'] ?? 'ATIVA',
            'criado_por_usuario_id' => $data['criado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function slaPolicyById(int $slaId): ?array
    {
        if (!$this->tableExists('sla_politicas')) {
            return null;
        }

        $statement = $this->pdo()->prepare(
            'SELECT
                id, conta_id, orgao_id, unidade_id, codigo_sla, nome_sla, prioridade,
                tempo_resposta_min, tempo_resolucao_min, status_sla
             FROM sla_politicas
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $slaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }
    public function suporteTickets(?string $ufSigla = null, int $limit = 180): array
    {
        if (!$this->tableExists('suporte_tickets')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('t', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 180, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                t.id,
                t.conta_id,
                c.nome_fantasia AS conta_nome,
                t.orgao_id,
                o.nome_oficial AS orgao_nome,
                t.unidade_id,
                u.nome_unidade,
                t.sla_politica_id,
                s.codigo_sla,
                t.titulo_ticket,
                t.prioridade,
                t.status_ticket,
                t.resposta_limite_em,
                t.resolucao_limite_em,
                t.aberto_em,
                ua.nome_completo AS aberto_por_nome,
                ut.nome_completo AS atribuido_para_nome
             FROM suporte_tickets t
             INNER JOIN contas c ON c.id = t.conta_id
             LEFT JOIN orgaos o ON o.id = t.orgao_id
             LEFT JOIN unidades u ON u.id = t.unidade_id
             LEFT JOIN sla_politicas s ON s.id = t.sla_politica_id
             LEFT JOIN usuarios ua ON ua.id = t.aberto_por_usuario_id
             LEFT JOIN usuarios ut ON ut.id = t.atribuido_para_usuario_id
             {$whereSql}
             ORDER BY t.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createSuporteTicket(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO suporte_tickets
                (
                    conta_id, orgao_id, unidade_id, sla_politica_id, titulo_ticket, descricao_ticket,
                    prioridade, status_ticket, aberto_por_usuario_id, atribuido_para_usuario_id,
                    aberto_em, resposta_limite_em, resolucao_limite_em, primeira_resposta_em,
                    resolvido_em, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :sla_politica_id, :titulo_ticket, :descricao_ticket,
                    :prioridade, :status_ticket, :aberto_por_usuario_id, :atribuido_para_usuario_id,
                    NOW(), :resposta_limite_em, :resolucao_limite_em, NULL,
                    NULL, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'sla_politica_id' => $data['sla_politica_id'] ?? null,
            'titulo_ticket' => $data['titulo_ticket'],
            'descricao_ticket' => $data['descricao_ticket'],
            'prioridade' => $data['prioridade'],
            'status_ticket' => $data['status_ticket'] ?? 'ABERTO',
            'aberto_por_usuario_id' => $data['aberto_por_usuario_id'],
            'atribuido_para_usuario_id' => $data['atribuido_para_usuario_id'] ?? null,
            'resposta_limite_em' => $data['resposta_limite_em'] ?? null,
            'resolucao_limite_em' => $data['resolucao_limite_em'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function digitalSignatures(?string $ufSigla = null, int $limit = 180): array
    {
        if (!$this->tableExists('assinaturas_digitais_registros')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('d', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 180, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                d.id,
                d.conta_id,
                c.nome_fantasia AS conta_nome,
                d.orgao_id,
                o.nome_oficial AS orgao_nome,
                d.unidade_id,
                u.nome_unidade,
                d.entidade_tipo,
                d.entidade_id,
                d.hash_documento,
                d.algoritmo_hash,
                d.certificado_ref,
                d.assinado_em,
                usr.nome_completo AS assinado_por_nome
             FROM assinaturas_digitais_registros d
             INNER JOIN contas c ON c.id = d.conta_id
             LEFT JOIN orgaos o ON o.id = d.orgao_id
             LEFT JOIN unidades u ON u.id = d.unidade_id
             INNER JOIN usuarios usr ON usr.id = d.assinado_por_usuario_id
             {$whereSql}
             ORDER BY d.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createDigitalSignatureRecord(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas_digitais_registros
                (
                    conta_id, orgao_id, unidade_id, entidade_tipo, entidade_id, hash_documento,
                    algoritmo_hash, certificado_ref, assinatura_payload_json,
                    assinado_por_usuario_id, assinado_em, created_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :entidade_tipo, :entidade_id, :hash_documento,
                    :algoritmo_hash, :certificado_ref, :assinatura_payload_json,
                    :assinado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'entidade_tipo' => $data['entidade_tipo'],
            'entidade_id' => $data['entidade_id'],
            'hash_documento' => $data['hash_documento'],
            'algoritmo_hash' => $data['algoritmo_hash'] ?? 'SHA256',
            'certificado_ref' => $data['certificado_ref'] ?? null,
            'assinatura_payload_json' => $this->jsonEncodeOrNull($data['assinatura_payload'] ?? null),
            'assinado_por_usuario_id' => $data['assinado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createExecutiveReport(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO relatorios_executivos_consolidados
                (
                    conta_id, orgao_id, unidade_id, periodo_inicio, periodo_fim,
                    filtros_json, resumo_json,
                    total_incidentes, total_plancons, total_alertas_ativos,
                    total_tickets_abertos, total_tickets_sla_vencido,
                    arquivo_caminho, gerado_por_usuario_id, gerado_em,
                    created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :periodo_inicio, :periodo_fim,
                    :filtros_json, :resumo_json,
                    :total_incidentes, :total_plancons, :total_alertas_ativos,
                    :total_tickets_abertos, :total_tickets_sla_vencido,
                    :arquivo_caminho, :gerado_por_usuario_id, NOW(),
                    NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'periodo_inicio' => $data['periodo_inicio'] ?? null,
            'periodo_fim' => $data['periodo_fim'] ?? null,
            'filtros_json' => $this->jsonEncodeOrNull($data['filtros'] ?? null),
            'resumo_json' => $this->jsonEncodeOrNull($data['resumo'] ?? null),
            'total_incidentes' => $data['total_incidentes'] ?? 0,
            'total_plancons' => $data['total_plancons'] ?? 0,
            'total_alertas_ativos' => $data['total_alertas_ativos'] ?? 0,
            'total_tickets_abertos' => $data['total_tickets_abertos'] ?? 0,
            'total_tickets_sla_vencido' => $data['total_tickets_sla_vencido'] ?? 0,
            'arquivo_caminho' => $data['arquivo_caminho'] ?? null,
            'gerado_por_usuario_id' => $data['gerado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function executiveReports(?string $ufSigla = null, int $limit = 120): array
    {
        if (!$this->tableExists('relatorios_executivos_consolidados')) {
            return [];
        }

        $where = [];
        $params = [];
        $this->applyUfWhere('r', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                r.id,
                r.conta_id,
                c.nome_fantasia AS conta_nome,
                r.orgao_id,
                o.nome_oficial AS orgao_nome,
                r.unidade_id,
                u.nome_unidade,
                r.periodo_inicio,
                r.periodo_fim,
                r.total_incidentes,
                r.total_plancons,
                r.total_alertas_ativos,
                r.total_tickets_abertos,
                r.total_tickets_sla_vencido,
                r.gerado_em,
                usr.nome_completo AS gerado_por_nome
             FROM relatorios_executivos_consolidados r
             INNER JOIN contas c ON c.id = r.conta_id
             LEFT JOIN orgaos o ON o.id = r.orgao_id
             LEFT JOIN unidades u ON u.id = r.unidade_id
             INNER JOIN usuarios usr ON usr.id = r.gerado_por_usuario_id
             {$whereSql}
             ORDER BY r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function executiveSummaryByScope(array $scope, ?string $dateFrom, ?string $dateTo): array
    {
        $summary = [
            'total_incidentes' => 0,
            'incidentes_ativos' => 0,
            'total_plancons' => 0,
            'total_alertas_ativos' => 0,
            'total_tickets_abertos' => 0,
            'total_tickets_sla_vencido' => 0,
        ];

        if ($this->tableExists('incidentes')) {
            $where = [];
            $params = [];
            $this->applyScopeWhere('i', $scope, $where, $params);
            $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
            $whereSql = $this->whereClause($where);

            $statement = $this->pdo()->prepare(
                "SELECT
                    COUNT(*) AS total_incidentes,
                    SUM(CASE WHEN i.status_incidente IN ('ABERTO', 'EM_ANDAMENTO') THEN 1 ELSE 0 END) AS incidentes_ativos
                 FROM incidentes i
                 {$whereSql}"
            );
            $statement->execute($params);
            $row = $statement->fetch() ?: [];
            $summary['total_incidentes'] = (int) ($row['total_incidentes'] ?? 0);
            $summary['incidentes_ativos'] = (int) ($row['incidentes_ativos'] ?? 0);
        }

        if ($this->tableExists('plancons')) {
            $where = [];
            $params = [];
            $this->applyScopeWhere('p', $scope, $where, $params);
            $whereSql = $this->whereClause($where);

            $statement = $this->pdo()->prepare(
                "SELECT COUNT(*) AS total_plancons
                 FROM plancons p
                 {$whereSql}"
            );
            $statement->execute($params);
            $summary['total_plancons'] = (int) $statement->fetchColumn();
        }

        if ($this->tableExists('inteligencia_alertas_operacionais')) {
            $where = ["a.status_alerta = 'ATIVO'"];
            $params = [];
            $this->applyScopeWhere('a', $scope, $where, $params);
            $whereSql = $this->whereClause($where);

            $statement = $this->pdo()->prepare(
                "SELECT COUNT(*)
                 FROM inteligencia_alertas_operacionais a
                 {$whereSql}"
            );
            $statement->execute($params);
            $summary['total_alertas_ativos'] = (int) $statement->fetchColumn();
        }

        if ($this->tableExists('suporte_tickets')) {
            $where = ["t.status_ticket IN ('ABERTO', 'EM_ATENDIMENTO')"];
            $params = [];
            $this->applyScopeWhere('t', $scope, $where, $params);
            $whereSql = $this->whereClause($where);

            $statement = $this->pdo()->prepare(
                "SELECT COUNT(*)
                 FROM suporte_tickets t
                 {$whereSql}"
            );
            $statement->execute($params);
            $summary['total_tickets_abertos'] = (int) $statement->fetchColumn();

            $whereOverdue = [
                "t.status_ticket IN ('ABERTO', 'EM_ATENDIMENTO')",
                "((t.resposta_limite_em IS NOT NULL AND t.primeira_resposta_em IS NULL AND t.resposta_limite_em < NOW()) OR (t.resolucao_limite_em IS NOT NULL AND t.resolvido_em IS NULL AND t.resolucao_limite_em < NOW()))",
            ];
            $paramsOverdue = [];
            $this->applyScopeWhere('t', $scope, $whereOverdue, $paramsOverdue);
            $whereOverdueSql = $this->whereClause($whereOverdue);

            $statement = $this->pdo()->prepare(
                "SELECT COUNT(*)
                 FROM suporte_tickets t
                 {$whereOverdueSql}"
            );
            $statement->execute($paramsOverdue);
            $summary['total_tickets_sla_vencido'] = (int) $statement->fetchColumn();
        }

        return $summary;
    }

    private function countScoped(string $table, ?string $extraCondition, ?string $ufSigla): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        $where = [];
        $params = [];
        if ($extraCondition !== null) {
            $where[] = $extraCondition;
        }

        $alias = 't';
        $this->applyUfWhere($alias, $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM {$table} {$alias}
             {$whereSql}"
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private function applyUfWhere(string $alias, ?string $ufSigla, array &$where, array &$params): void
    {
        $uf = strtoupper(trim((string) $ufSigla));
        if (strlen($uf) !== 2) {
            return;
        }

        $where[] = "{$alias}.conta_id IN (SELECT c.id FROM contas c WHERE c.uf_sigla = :uf_sigla)";
        $params['uf_sigla'] = $uf;
    }

    private function applyScopeWhere(string $alias, array $scope, array &$where, array &$params): void
    {
        $contaId = (int) ($scope['conta_id'] ?? 0);
        if ($contaId < 1) {
            $where[] = '1 = 0';
            return;
        }

        $where[] = "{$alias}.conta_id = :conta_id";
        $params['conta_id'] = $contaId;

        $orgaoId = isset($scope['orgao_id']) ? (int) $scope['orgao_id'] : 0;
        if ($orgaoId > 0) {
            $where[] = "{$alias}.orgao_id = :orgao_id";
            $params['orgao_id'] = $orgaoId;
        }

        $unidadeId = isset($scope['unidade_id']) ? (int) $scope['unidade_id'] : 0;
        if ($unidadeId > 0) {
            $where[] = "{$alias}.unidade_id = :unidade_id";
            $params['unidade_id'] = $unidadeId;
        }
    }

    private function applyDateRange(
        string $column,
        ?string $dateFrom,
        ?string $dateTo,
        array &$where,
        array &$params
    ): void {
        if ($dateFrom !== null) {
            $where[] = "{$column} >= :date_from";
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null) {
            $where[] = "{$column} <= :date_to";
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
    }

    private function whereClause(array $where): string
    {
        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
    }

    private function normalizeLimit(int $limit, int $default, int $max): int
    {
        if ($limit < 1) {
            return $default;
        }

        return min($limit, $max);
    }

    private function jsonEncodeOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? null : $json;
    }

    private function decodeJsonArray(mixed $json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
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

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
