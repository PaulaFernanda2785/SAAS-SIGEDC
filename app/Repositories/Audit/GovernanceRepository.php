<?php

declare(strict_types=1);

namespace App\Repositories\Audit;

use App\Support\Database;
use PDO;

final class GovernanceRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function summary(array $scope, ?string $dateFrom, ?string $dateTo): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('l', $scope, $where, $params);
        $this->applyDateRange('l.created_at', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT
                COUNT(*) AS total_eventos,
                SUM(CASE WHEN l.resultado = 'SUCESSO' THEN 1 ELSE 0 END) AS total_sucesso,
                SUM(CASE WHEN l.resultado = 'FALHA' THEN 1 ELSE 0 END) AS total_falha,
                SUM(CASE WHEN l.resultado = 'NEGADO' THEN 1 ELSE 0 END) AS total_negado
             FROM logs_auditoria l
             {$whereSql}"
        );
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        return [
            'total_eventos' => (int) ($row['total_eventos'] ?? 0),
            'total_sucesso' => (int) ($row['total_sucesso'] ?? 0),
            'total_falha' => (int) ($row['total_falha'] ?? 0),
            'total_negado' => (int) ($row['total_negado'] ?? 0),
        ];
    }

    public function actionFrequency(array $scope, ?string $dateFrom, ?string $dateTo, int $limit = 20): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('l', $scope, $where, $params);
        $this->applyDateRange('l.created_at', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 20, 120);

        $statement = $this->pdo()->prepare(
            "SELECT l.modulo_codigo, l.acao, l.resultado, COUNT(*) AS total
             FROM logs_auditoria l
             {$whereSql}
             GROUP BY l.modulo_codigo, l.acao, l.resultado
             ORDER BY total DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recentLogs(array $scope, ?string $resultado, ?string $moduloCodigo, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('l', $scope, $where, $params);

        if ($resultado !== null) {
            $where[] = 'l.resultado = :resultado';
            $params['resultado'] = $resultado;
        }

        if ($moduloCodigo !== null) {
            $where[] = 'l.modulo_codigo = :modulo_codigo';
            $params['modulo_codigo'] = $moduloCodigo;
        }

        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT
                l.id,
                l.modulo_codigo,
                l.acao,
                l.resultado,
                l.entidade_tipo,
                l.entidade_id,
                l.ip_address,
                l.created_at,
                u.nome_completo AS usuario_nome
             FROM logs_auditoria l
             LEFT JOIN usuarios u ON u.id = l.usuario_id
             {$whereSql}
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function hasTermAcceptance(int $usuarioId, string $termoCodigo, string $versaoTermo): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM governanca_termos_aceite
             WHERE usuario_id = :usuario_id
               AND termo_codigo = :termo_codigo
               AND versao_termo = :versao_termo
             LIMIT 1'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'termo_codigo' => $termoCodigo,
            'versao_termo' => $versaoTermo,
        ]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    public function registerTermAcceptance(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO governanca_termos_aceite
                (conta_id, orgao_id, unidade_id, usuario_id, termo_codigo, versao_termo, aceito_em, origem_ip, user_agent, detalhes_json, created_at, updated_at)
             VALUES
                (:conta_id, :orgao_id, :unidade_id, :usuario_id, :termo_codigo, :versao_termo, NOW(), :origem_ip, :user_agent, :detalhes_json, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                aceito_em = VALUES(aceito_em),
                origem_ip = VALUES(origem_ip),
                user_agent = VALUES(user_agent),
                detalhes_json = VALUES(detalhes_json),
                updated_at = NOW()'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'usuario_id' => $data['usuario_id'],
            'termo_codigo' => $data['termo_codigo'],
            'versao_termo' => $data['versao_termo'],
            'origem_ip' => $data['origem_ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'detalhes_json' => json_encode($data['detalhes'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function recentTermAcceptances(array $scope, int $limit = 50): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('g', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 50, 200);

        $statement = $this->pdo()->prepare(
            "SELECT
                g.id,
                g.termo_codigo,
                g.versao_termo,
                g.aceito_em,
                u.nome_completo AS usuario_nome
             FROM governanca_termos_aceite g
             INNER JOIN usuarios u ON u.id = g.usuario_id
             {$whereSql}
             ORDER BY g.aceito_em DESC, g.id DESC
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

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
