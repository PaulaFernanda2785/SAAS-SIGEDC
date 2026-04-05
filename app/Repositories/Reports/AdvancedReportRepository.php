<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\Support\Database;
use PDO;

final class AdvancedReportRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function registerExecution(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO relatorios_avancados_execucoes
                (
                    conta_id, orgao_id, unidade_id, usuario_id, tipo_relatorio, filtros_json,
                    status_execucao, total_registros, arquivo_caminho, gerado_em, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :usuario_id, :tipo_relatorio, :filtros_json,
                    :status_execucao, :total_registros, :arquivo_caminho, NOW(), NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'usuario_id' => $data['usuario_id'],
            'tipo_relatorio' => $data['tipo_relatorio'],
            'filtros_json' => json_encode($data['filtros'] ?? [], JSON_UNESCAPED_UNICODE),
            'status_execucao' => $data['status_execucao'] ?? 'CONCLUIDO',
            'total_registros' => $data['total_registros'] ?? 0,
            'arquivo_caminho' => $data['arquivo_caminho'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function recentExecutions(array $scope, int $limit = 40): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 40, 200);

        $statement = $this->pdo()->prepare(
            "SELECT
                r.id,
                r.tipo_relatorio,
                r.status_execucao,
                r.total_registros,
                r.gerado_em,
                u.nome_completo AS usuario_nome
             FROM relatorios_avancados_execucoes r
             LEFT JOIN usuarios u ON u.id = r.usuario_id
             {$whereSql}
             ORDER BY r.gerado_em DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
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
