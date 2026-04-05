<?php

declare(strict_types=1);

namespace App\Repositories\Operational;

use App\Support\Database;
use PDO;

final class UnitRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function optionsByScope(array $scope, int $limit = 250): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('u', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 250, 500);

        $statement = $this->pdo()->prepare(
            "SELECT
                u.id,
                u.orgao_id,
                o.nome_oficial AS orgao_nome,
                u.codigo_unidade,
                u.nome_unidade,
                u.uf_sigla,
                u.status_unidade
             FROM unidades u
             INNER JOIN orgaos o ON o.id = u.orgao_id
             {$whereSql}
             ORDER BY u.nome_unidade ASC, u.id ASC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function existsInScope(array $scope, int $unidadeId): bool
    {
        $where = ['u.id = :unidade_id'];
        $params = ['unidade_id' => $unidadeId];
        $this->applyScopeWhere('u', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM unidades u
             {$whereSql}
             LIMIT 1"
        );
        $statement->execute($params);

        return ((int) $statement->fetchColumn()) > 0;
    }

    private function applyScopeWhere(string $alias, array $scope, array &$where, array &$params): void
    {
        $contaId = (int) ($scope['conta_id'] ?? 0);
        if ($contaId < 1) {
            $where[] = '1 = 0';
            return;
        }

        $where[] = "{$alias}.orgao_id IN (
            SELECT id
            FROM orgaos
            WHERE conta_id = :conta_id
        )";
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

            $where[] = "{$alias}.id = :scope_unidade_id";
            $params['scope_unidade_id'] = $unidadeId;
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
