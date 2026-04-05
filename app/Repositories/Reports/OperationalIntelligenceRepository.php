<?php

declare(strict_types=1);

namespace App\Repositories\Reports;

use App\Support\Database;
use PDO;

final class OperationalIntelligenceRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function incidentStatusDistribution(array $scope, ?string $dateFrom, ?string $dateTo): array
    {
        $where = [];
        $params = [];
        $this->applyIncidentScopeWhere('i', $scope, $where, $params);
        $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT i.status_incidente, COUNT(*) AS total
             FROM incidentes i
             {$whereSql}
             GROUP BY i.status_incidente
             ORDER BY total DESC"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function municipalityHotspots(array $scope, ?string $dateFrom, ?string $dateTo, int $limit = 20): array
    {
        $where = [];
        $params = [];
        $this->applyIncidentScopeWhere('i', $scope, $where, $params);
        $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 20, 100);

        $statement = $this->pdo()->prepare(
            "SELECT
                i.municipio,
                COUNT(*) AS total_incidentes,
                SUM(CASE WHEN i.status_incidente IN ('ABERTO', 'EM_ANDAMENTO') THEN 1 ELSE 0 END) AS incidentes_ativos
             FROM incidentes i
             {$whereSql}
             GROUP BY i.municipio
             ORDER BY total_incidentes DESC, incidentes_ativos DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function mapPoints(array $scope, ?string $dateFrom, ?string $dateTo, string $fallbackUf, int $limit = 40): array
    {
        $where = [];
        $params = [];
        $this->applyIncidentScopeWhere('i', $scope, $where, $params);
        $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 40, 200);
        $params['fallback_uf'] = $fallbackUf;

        $statement = $this->pdo()->prepare(
            "SELECT
                base.municipio_nome,
                base.uf_sigla_ref,
                COUNT(*) AS total_incidentes,
                tm.latitude,
                tm.longitude
             FROM (
                SELECT
                    i.id,
                    TRIM(SUBSTRING_INDEX(i.municipio, '/', 1)) AS municipio_nome,
                    CASE
                        WHEN LOCATE('/', i.municipio) > 0 THEN UPPER(TRIM(SUBSTRING_INDEX(i.municipio, '/', -1)))
                        ELSE :fallback_uf
                    END AS uf_sigla_ref
                FROM incidentes i
                {$whereSql}
             ) base
             LEFT JOIN territorios_municipios tm
                ON tm.nome_municipio = base.municipio_nome
               AND tm.uf_sigla = base.uf_sigla_ref
             GROUP BY base.municipio_nome, base.uf_sigla_ref, tm.latitude, tm.longitude
             ORDER BY total_incidentes DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function responseTimeKpi(array $scope, ?string $dateFrom, ?string $dateTo): array
    {
        $where = [];
        $params = [];
        $this->applyIncidentScopeWhere('i', $scope, $where, $params);
        $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT
                COUNT(*) AS total_incidentes,
                SUM(CASE WHEN b.primeiro_briefing_em IS NULL THEN 1 ELSE 0 END) AS incidentes_sem_briefing,
                SUM(CASE WHEN b.primeiro_briefing_em IS NOT NULL THEN 1 ELSE 0 END) AS incidentes_com_briefing,
                AVG(TIMESTAMPDIFF(MINUTE, i.data_hora_abertura, b.primeiro_briefing_em)) AS media_minutos_primeiro_briefing,
                MIN(TIMESTAMPDIFF(MINUTE, i.data_hora_abertura, b.primeiro_briefing_em)) AS menor_tempo_briefing,
                MAX(TIMESTAMPDIFF(MINUTE, i.data_hora_abertura, b.primeiro_briefing_em)) AS maior_tempo_briefing
             FROM incidentes i
             LEFT JOIN (
                 SELECT incidente_id, MIN(created_at) AS primeiro_briefing_em
                 FROM incidentes_briefing
                 GROUP BY incidente_id
             ) b ON b.incidente_id = i.id
             {$whereSql}"
        );
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        return [
            'total_incidentes' => (int) ($row['total_incidentes'] ?? 0),
            'incidentes_sem_briefing' => (int) ($row['incidentes_sem_briefing'] ?? 0),
            'incidentes_com_briefing' => (int) ($row['incidentes_com_briefing'] ?? 0),
            'media_minutos_primeiro_briefing' => isset($row['media_minutos_primeiro_briefing']) ? (float) $row['media_minutos_primeiro_briefing'] : null,
            'menor_tempo_briefing' => isset($row['menor_tempo_briefing']) ? (int) $row['menor_tempo_briefing'] : null,
            'maior_tempo_briefing' => isset($row['maior_tempo_briefing']) ? (int) $row['maior_tempo_briefing'] : null,
        ];
    }

    public function planconCoverage(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT
                COUNT(*) AS total_plancons,
                SUM(CASE WHEN p.status_plancon = 'ATIVO' THEN 1 ELSE 0 END) AS total_plancons_ativos,
                SUM(CASE WHEN p.vigencia_fim IS NOT NULL AND p.vigencia_fim < CURDATE() THEN 1 ELSE 0 END) AS total_plancons_vencidos
             FROM plancons p
             {$whereSql}"
        );
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        return [
            'total_plancons' => (int) ($row['total_plancons'] ?? 0),
            'total_plancons_ativos' => (int) ($row['total_plancons_ativos'] ?? 0),
            'total_plancons_vencidos' => (int) ($row['total_plancons_vencidos'] ?? 0),
        ];
    }

    public function trendByDay(array $scope, ?string $dateFrom, ?string $dateTo, int $limit = 30): array
    {
        $where = [];
        $params = [];
        $this->applyIncidentScopeWhere('i', $scope, $where, $params);
        $this->applyDateRange('i.data_hora_abertura', $dateFrom, $dateTo, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 30, 120);

        $statement = $this->pdo()->prepare(
            "SELECT
                DATE(i.data_hora_abertura) AS referencia_data,
                COUNT(*) AS total_incidentes,
                SUM(CASE WHEN i.status_incidente IN ('ABERTO', 'EM_ANDAMENTO') THEN 1 ELSE 0 END) AS incidentes_ativos
             FROM incidentes i
             {$whereSql}
             GROUP BY DATE(i.data_hora_abertura)
             ORDER BY referencia_data DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    private function applyIncidentScopeWhere(string $alias, array $scope, array &$where, array &$params): void
    {
        $this->applyScopeWhere($alias, $scope, $where, $params);
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
