<?php

declare(strict_types=1);

namespace App\Repositories\Operational;

use App\Support\Database;
use PDO;

final class IncidentExpansionRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function summary(array $scope): array
    {
        return [
            'pai' => $this->countTable('incidentes_estrategias_pai', $scope),
            'operacoes' => $this->countTable('incidentes_operacoes_campo', $scope),
            'planejamento' => $this->countTable('incidentes_planejamento_situacao', $scope),
            'seguranca' => $this->countTable('incidentes_seguranca', $scope),
            'desmobilizacao' => $this->countTable('incidentes_desmobilizacao', $scope),
        ];
    }

    public function recentPai(array $scope, int $limit = 120): array
    {
        return $this->recentByTable(
            table: 'incidentes_estrategias_pai',
            alias: 'x',
            select: 'x.id, x.incidente_id, x.versao_pai, x.status_pai, x.created_at, i.numero_ocorrencia, i.nome_incidente',
            join: 'INNER JOIN incidentes i ON i.id = x.incidente_id',
            scope: $scope,
            limit: $limit
        );
    }

    public function recentOperations(array $scope, int $limit = 120): array
    {
        return $this->recentByTable(
            table: 'incidentes_operacoes_campo',
            alias: 'o',
            select: 'o.id, o.incidente_id, o.frente_operacional, o.status_operacao, o.created_at, i.numero_ocorrencia, i.nome_incidente',
            join: 'INNER JOIN incidentes i ON i.id = o.incidente_id',
            scope: $scope,
            limit: $limit
        );
    }

    public function recentPlanning(array $scope, int $limit = 120): array
    {
        return $this->recentByTable(
            table: 'incidentes_planejamento_situacao',
            alias: 'p',
            select: 'p.id, p.incidente_id, p.status_planejamento, p.created_at, i.numero_ocorrencia, i.nome_incidente',
            join: 'INNER JOIN incidentes i ON i.id = p.incidente_id',
            scope: $scope,
            limit: $limit
        );
    }

    public function recentSafety(array $scope, int $limit = 120): array
    {
        return $this->recentByTable(
            table: 'incidentes_seguranca',
            alias: 's',
            select: 's.id, s.incidente_id, s.status_seguranca, s.created_at, i.numero_ocorrencia, i.nome_incidente',
            join: 'INNER JOIN incidentes i ON i.id = s.incidente_id',
            scope: $scope,
            limit: $limit
        );
    }

    public function recentDemobilization(array $scope, int $limit = 120): array
    {
        return $this->recentByTable(
            table: 'incidentes_desmobilizacao',
            alias: 'd',
            select: 'd.id, d.incidente_id, d.status_desmobilizacao, d.created_at, i.numero_ocorrencia, i.nome_incidente',
            join: 'INNER JOIN incidentes i ON i.id = d.incidente_id',
            scope: $scope,
            limit: $limit
        );
    }

    public function createPai(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_estrategias_pai
                (
                    incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, versao_pai, estrategia_geral, taticas_prioritarias,
                    atividades_planejadas, responsavel_execucao, recursos_necessarios, areas_prioritarias, status_pai,
                    registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :periodo_operacional_id, :conta_id, :orgao_id, :unidade_id, :versao_pai, :estrategia_geral, :taticas_prioritarias,
                    :atividades_planejadas, :responsavel_execucao, :recursos_necessarios, :areas_prioritarias, :status_pai,
                    :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'periodo_operacional_id' => $data['periodo_operacional_id'] ?? null,
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'versao_pai' => $data['versao_pai'],
            'estrategia_geral' => $data['estrategia_geral'],
            'taticas_prioritarias' => $data['taticas_prioritarias'] ?? null,
            'atividades_planejadas' => $data['atividades_planejadas'] ?? null,
            'responsavel_execucao' => $data['responsavel_execucao'] ?? null,
            'recursos_necessarios' => $data['recursos_necessarios'] ?? null,
            'areas_prioritarias' => $data['areas_prioritarias'] ?? null,
            'status_pai' => $data['status_pai'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createOperation(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_operacoes_campo
                (
                    incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, frente_operacional, setor_operacional, supervisor_frente,
                    missao_tatica, recursos_designados, situacao_atual, resultados_parciais, status_operacao, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :periodo_operacional_id, :conta_id, :orgao_id, :unidade_id, :frente_operacional, :setor_operacional, :supervisor_frente,
                    :missao_tatica, :recursos_designados, :situacao_atual, :resultados_parciais, :status_operacao, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'periodo_operacional_id' => $data['periodo_operacional_id'] ?? null,
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'frente_operacional' => $data['frente_operacional'],
            'setor_operacional' => $data['setor_operacional'] ?? null,
            'supervisor_frente' => $data['supervisor_frente'] ?? null,
            'missao_tatica' => $data['missao_tatica'] ?? null,
            'recursos_designados' => $data['recursos_designados'] ?? null,
            'situacao_atual' => $data['situacao_atual'] ?? null,
            'resultados_parciais' => $data['resultados_parciais'] ?? null,
            'status_operacao' => $data['status_operacao'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createPlanning(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_planejamento_situacao
                (
                    incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, situacao_consolidada, prognostico, cenario_provavel,
                    pendencias_criticas, escalonamento_recomendado, status_planejamento, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :periodo_operacional_id, :conta_id, :orgao_id, :unidade_id, :situacao_consolidada, :prognostico, :cenario_provavel,
                    :pendencias_criticas, :escalonamento_recomendado, :status_planejamento, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'periodo_operacional_id' => $data['periodo_operacional_id'] ?? null,
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'situacao_consolidada' => $data['situacao_consolidada'],
            'prognostico' => $data['prognostico'] ?? null,
            'cenario_provavel' => $data['cenario_provavel'] ?? null,
            'pendencias_criticas' => $data['pendencias_criticas'] ?? null,
            'escalonamento_recomendado' => $data['escalonamento_recomendado'] ?? null,
            'status_planejamento' => $data['status_planejamento'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createSafety(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_seguranca
                (
                    incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, riscos_operacionais, equipes_expostas,
                    medidas_controle, epis_recomendados, restricoes_operacionais, interdicoes, status_seguranca, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :periodo_operacional_id, :conta_id, :orgao_id, :unidade_id, :riscos_operacionais, :equipes_expostas,
                    :medidas_controle, :epis_recomendados, :restricoes_operacionais, :interdicoes, :status_seguranca, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'periodo_operacional_id' => $data['periodo_operacional_id'] ?? null,
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'riscos_operacionais' => $data['riscos_operacionais'],
            'equipes_expostas' => $data['equipes_expostas'] ?? null,
            'medidas_controle' => $data['medidas_controle'] ?? null,
            'epis_recomendados' => $data['epis_recomendados'] ?? null,
            'restricoes_operacionais' => $data['restricoes_operacionais'] ?? null,
            'interdicoes' => $data['interdicoes'] ?? null,
            'status_seguranca' => $data['status_seguranca'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createDemobilization(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_desmobilizacao
                (
                    incidente_id, conta_id, orgao_id, unidade_id, criterios_desmobilizacao, recursos_liberados, pendencias_finais,
                    licoes_iniciais, situacao_final, data_hora_inicio, data_hora_encerramento, status_desmobilizacao,
                    registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :conta_id, :orgao_id, :unidade_id, :criterios_desmobilizacao, :recursos_liberados, :pendencias_finais,
                    :licoes_iniciais, :situacao_final, :data_hora_inicio, :data_hora_encerramento, :status_desmobilizacao,
                    :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'criterios_desmobilizacao' => $data['criterios_desmobilizacao'],
            'recursos_liberados' => $data['recursos_liberados'] ?? null,
            'pendencias_finais' => $data['pendencias_finais'] ?? null,
            'licoes_iniciais' => $data['licoes_iniciais'] ?? null,
            'situacao_final' => $data['situacao_final'] ?? null,
            'data_hora_inicio' => $data['data_hora_inicio'] ?? null,
            'data_hora_encerramento' => $data['data_hora_encerramento'] ?? null,
            'status_desmobilizacao' => $data['status_desmobilizacao'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    private function countTable(string $table, array $scope): int
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('t', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare("SELECT COUNT(*) FROM {$table} t {$whereSql}");
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private function recentByTable(
        string $table,
        string $alias,
        string $select,
        string $join,
        array $scope,
        int $limit
    ): array {
        $where = [];
        $params = [];
        $this->applyScopeWhere($alias, $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT {$select}
             FROM {$table} {$alias}
             {$join}
             {$whereSql}
             ORDER BY {$alias}.created_at DESC, {$alias}.id DESC
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
