<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\Support\Database;
use PDO;

final class OperationalAlertRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function upsertScopeAlert(
        array $scope,
        string $alertCode,
        string $level,
        string $message,
        ?int $incidentId = null,
        string $origin = 'REGRAS_FASE4',
        array $metadata = []
    ): void {
        $scopeIds = $this->normalizedScopeIds($scope);
        if ($scopeIds['conta_id'] < 1 || $scopeIds['orgao_id'] < 1) {
            return;
        }

        $current = $this->activeAlertByCode($scopeIds, $alertCode, $incidentId);
        if ($current !== null) {
            $statement = $this->pdo()->prepare(
                'UPDATE inteligencia_alertas_operacionais
                 SET nivel_alerta = :nivel_alerta,
                     mensagem_alerta = :mensagem_alerta,
                     origem_geracao = :origem_geracao,
                     metadados_json = :metadados_json,
                     gerado_em = NOW(),
                     status_alerta = :status_alerta,
                     updated_at = NOW()
                 WHERE id = :id
                 LIMIT 1'
            );
            $statement->execute([
                'id' => $current['id'],
                'nivel_alerta' => $level,
                'mensagem_alerta' => $message,
                'origem_geracao' => $origin,
                'metadados_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'status_alerta' => 'ATIVO',
            ]);
            return;
        }

        $statement = $this->pdo()->prepare(
            'INSERT INTO inteligencia_alertas_operacionais
                (
                    conta_id, orgao_id, unidade_id, incidente_id, alerta_codigo, nivel_alerta, mensagem_alerta,
                    status_alerta, origem_geracao, metadados_json, gerado_em, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :incidente_id, :alerta_codigo, :nivel_alerta, :mensagem_alerta,
                    :status_alerta, :origem_geracao, :metadados_json, NOW(), NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $scopeIds['conta_id'],
            'orgao_id' => $scopeIds['orgao_id'],
            'unidade_id' => $scopeIds['unidade_id'],
            'incidente_id' => $incidentId,
            'alerta_codigo' => $alertCode,
            'nivel_alerta' => $level,
            'mensagem_alerta' => $message,
            'status_alerta' => 'ATIVO',
            'origem_geracao' => $origin,
            'metadados_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function closeScopeAlert(array $scope, string $alertCode, ?int $incidentId = null): void
    {
        $scopeIds = $this->normalizedScopeIds($scope);
        if ($scopeIds['conta_id'] < 1 || $scopeIds['orgao_id'] < 1) {
            return;
        }

        $where = [
            'conta_id = :conta_id',
            'orgao_id = :orgao_id',
            'alerta_codigo = :alerta_codigo',
            "status_alerta = 'ATIVO'",
        ];
        $params = [
            'conta_id' => $scopeIds['conta_id'],
            'orgao_id' => $scopeIds['orgao_id'],
            'alerta_codigo' => $alertCode,
            'status_alerta' => 'MITIGADO',
        ];

        if ($scopeIds['unidade_id'] !== null) {
            $where[] = 'unidade_id = :unidade_id';
            $params['unidade_id'] = $scopeIds['unidade_id'];
        } else {
            $where[] = 'unidade_id IS NULL';
        }

        if ($incidentId !== null && $incidentId > 0) {
            $where[] = 'incidente_id = :incidente_id';
            $params['incidente_id'] = $incidentId;
        } else {
            $where[] = 'incidente_id IS NULL';
        }

        $sql = sprintf(
            'UPDATE inteligencia_alertas_operacionais
             SET status_alerta = :status_alerta, updated_at = NOW()
             WHERE %s',
            implode(' AND ', $where)
        );

        $this->pdo()->prepare($sql)->execute($params);
    }

    public function activeAlerts(array $scope, int $limit = 20): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('a', $scope, $where, $params);
        $where[] = "a.status_alerta = 'ATIVO'";
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 20, 120);

        $statement = $this->pdo()->prepare(
            "SELECT
                a.id,
                a.alerta_codigo,
                a.nivel_alerta,
                a.mensagem_alerta,
                a.status_alerta,
                a.origem_geracao,
                a.gerado_em,
                i.numero_ocorrencia
             FROM inteligencia_alertas_operacionais a
             LEFT JOIN incidentes i ON i.id = a.incidente_id
             {$whereSql}
             ORDER BY FIELD(a.nivel_alerta, 'CRITICO', 'ALTO', 'MODERADO', 'BAIXO'), a.gerado_em DESC, a.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function activeAlertByCode(array $scopeIds, string $alertCode, ?int $incidentId): ?array
    {
        $where = [
            'conta_id = :conta_id',
            'orgao_id = :orgao_id',
            'alerta_codigo = :alerta_codigo',
            "status_alerta = 'ATIVO'",
        ];
        $params = [
            'conta_id' => $scopeIds['conta_id'],
            'orgao_id' => $scopeIds['orgao_id'],
            'alerta_codigo' => $alertCode,
        ];

        if ($scopeIds['unidade_id'] !== null) {
            $where[] = 'unidade_id = :unidade_id';
            $params['unidade_id'] = $scopeIds['unidade_id'];
        } else {
            $where[] = 'unidade_id IS NULL';
        }

        if ($incidentId !== null && $incidentId > 0) {
            $where[] = 'incidente_id = :incidente_id';
            $params['incidente_id'] = $incidentId;
        } else {
            $where[] = 'incidente_id IS NULL';
        }

        $sql = sprintf(
            'SELECT id FROM inteligencia_alertas_operacionais WHERE %s LIMIT 1',
            implode(' AND ', $where)
        );

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    private function normalizedScopeIds(array $scope): array
    {
        $contaId = (int) ($scope['conta_id'] ?? 0);
        $orgaoId = (int) ($scope['orgao_id'] ?? 0);
        $unidadeId = (int) ($scope['unidade_id'] ?? 0);

        return [
            'conta_id' => $contaId,
            'orgao_id' => $orgaoId,
            'unidade_id' => $unidadeId > 0 ? $unidadeId : null,
        ];
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

        if (($scope['restrict_to_orgao'] ?? false) === true) {
            $orgaoId = (int) ($scope['orgao_id'] ?? 0);
            if ($orgaoId < 1) {
                $where[] = '1 = 0';
                return;
            }

            $where[] = "{$alias}.orgao_id = :orgao_id";
            $params['orgao_id'] = $orgaoId;
        }

        if (($scope['restrict_to_unidade'] ?? false) === true) {
            $unidadeId = (int) ($scope['unidade_id'] ?? 0);
            if ($unidadeId < 1) {
                $where[] = '1 = 0';
                return;
            }

            $where[] = "{$alias}.unidade_id = :unidade_id";
            $params['unidade_id'] = $unidadeId;
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

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}

