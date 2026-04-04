<?php

declare(strict_types=1);

namespace App\Repositories\Plancon;

use App\Support\Database;
use PDO;

final class PlanconRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function summary(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT
                COUNT(*) AS total_plancons,
                SUM(CASE WHEN p.status_plancon = 'ATIVO' THEN 1 ELSE 0 END) AS plancons_ativos,
                SUM(CASE WHEN p.status_plancon = 'EM_REVISAO' THEN 1 ELSE 0 END) AS plancons_em_revisao,
                SUM(CASE WHEN p.status_plancon = 'VENCIDO' THEN 1 ELSE 0 END) AS plancons_vencidos
             FROM plancons p
             {$whereSql}"
        );
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        return [
            'total_plancons' => (int) ($row['total_plancons'] ?? 0),
            'plancons_ativos' => (int) ($row['plancons_ativos'] ?? 0),
            'plancons_em_revisao' => (int) ($row['plancons_em_revisao'] ?? 0),
            'plancons_vencidos' => (int) ($row['plancons_vencidos'] ?? 0),
        ];
    }

    public function plancons(array $scope, int $limit = 150): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 150, 500);

        $statement = $this->pdo()->prepare(
            "SELECT p.id, p.titulo_plano, p.versao_documento, p.municipio_estado, p.tipo_desastre_principal,
                    p.status_plancon, p.vigencia_inicio, p.vigencia_fim, p.updated_at
             FROM plancons p
             {$whereSql}
             ORDER BY p.updated_at DESC, p.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function planconOptions(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT p.id, p.titulo_plano, p.versao_documento, p.status_plancon
             FROM plancons p
             {$whereSql}
             ORDER BY p.updated_at DESC, p.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recentRisks(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT r.id, r.plancon_id, r.tipo_ameaca, r.nivel_risco, r.descricao_risco,
                    p.titulo_plano, p.versao_documento, r.created_at
             FROM plancon_riscos r
             INNER JOIN plancons p ON p.id = r.plancon_id
             {$whereSql}
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recentScenarios(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('c', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT c.id, c.plancon_id, c.nome_cenario, c.tipo_desastre_associado, c.classificacao_cenario,
                    p.titulo_plano, c.created_at
             FROM plancon_cenarios c
             INNER JOIN plancons p ON p.id = c.plancon_id
             {$whereSql}
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recentResources(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT r.id, r.plancon_id, r.tipo_recurso, r.categoria_recurso, r.status_recurso,
                    r.quantidade_disponivel, r.unidade_medida, p.titulo_plano, r.created_at
             FROM plancon_recursos r
             INNER JOIN plancons p ON p.id = r.plancon_id
             {$whereSql}
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function findPlanconById(array $scope, int $planconId): ?array
    {
        $where = ['p.id = :plancon_id'];
        $params = ['plancon_id' => $planconId];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT p.*
             FROM plancons p
             {$whereSql}
             LIMIT 1"
        );
        $statement->execute($params);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createPlancon(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancons
                (
                    conta_id, orgao_id, unidade_id, titulo_plano, municipio_estado, versao_documento, data_elaboracao, data_ultima_atualizacao,
                    responsavel_tecnico, contato_institucional, vigencia_inicio, vigencia_fim, area_abrangencia, tipo_desastre_principal,
                    outros_desastres_associados, base_legal_utilizada, objetivo_geral, objetivos_especificos, publico_alvo, status_plancon,
                    observacoes_gerais, criado_por_usuario_id, atualizado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :titulo_plano, :municipio_estado, :versao_documento, :data_elaboracao, :data_ultima_atualizacao,
                    :responsavel_tecnico, :contato_institucional, :vigencia_inicio, :vigencia_fim, :area_abrangencia, :tipo_desastre_principal,
                    :outros_desastres_associados, :base_legal_utilizada, :objetivo_geral, :objetivos_especificos, :publico_alvo, :status_plancon,
                    :observacoes_gerais, :criado_por_usuario_id, :atualizado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'titulo_plano' => $data['titulo_plano'],
            'municipio_estado' => $data['municipio_estado'] ?? null,
            'versao_documento' => $data['versao_documento'] ?? null,
            'data_elaboracao' => $data['data_elaboracao'] ?? null,
            'data_ultima_atualizacao' => $data['data_ultima_atualizacao'] ?? null,
            'responsavel_tecnico' => $data['responsavel_tecnico'] ?? null,
            'contato_institucional' => $data['contato_institucional'] ?? null,
            'vigencia_inicio' => $data['vigencia_inicio'] ?? null,
            'vigencia_fim' => $data['vigencia_fim'] ?? null,
            'area_abrangencia' => $data['area_abrangencia'] ?? null,
            'tipo_desastre_principal' => $data['tipo_desastre_principal'] ?? null,
            'outros_desastres_associados' => $data['outros_desastres_associados'] ?? null,
            'base_legal_utilizada' => $data['base_legal_utilizada'] ?? null,
            'objetivo_geral' => $data['objetivo_geral'] ?? null,
            'objetivos_especificos' => $data['objetivos_especificos'] ?? null,
            'publico_alvo' => $data['publico_alvo'] ?? null,
            'status_plancon' => $data['status_plancon'],
            'observacoes_gerais' => $data['observacoes_gerais'] ?? null,
            'criado_por_usuario_id' => $data['criado_por_usuario_id'],
            'atualizado_por_usuario_id' => $data['atualizado_por_usuario_id'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createRisk(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancon_riscos
                (
                    plancon_id, conta_id, orgao_id, unidade_id, tipo_ameaca, descricao_risco, origem_risco, historico_ocorrencias,
                    frequencia_ocorrencia, periodo_sazonal, areas_suscetiveis, populacao_exposta, infraestruturas_expostas, vulnerabilidades_identificadas,
                    capacidade_local_resposta, probabilidade_evento, impacto_potencial, nivel_risco, fatores_agravantes, fatores_atenuantes,
                    fontes_informacao_utilizadas, responsavel_analise, data_analise, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :plancon_id, :conta_id, :orgao_id, :unidade_id, :tipo_ameaca, :descricao_risco, :origem_risco, :historico_ocorrencias,
                    :frequencia_ocorrencia, :periodo_sazonal, :areas_suscetiveis, :populacao_exposta, :infraestruturas_expostas, :vulnerabilidades_identificadas,
                    :capacidade_local_resposta, :probabilidade_evento, :impacto_potencial, :nivel_risco, :fatores_agravantes, :fatores_atenuantes,
                    :fontes_informacao_utilizadas, :responsavel_analise, :data_analise, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'plancon_id' => $data['plancon_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'tipo_ameaca' => $data['tipo_ameaca'] ?? null,
            'descricao_risco' => $data['descricao_risco'],
            'origem_risco' => $data['origem_risco'] ?? null,
            'historico_ocorrencias' => $data['historico_ocorrencias'] ?? null,
            'frequencia_ocorrencia' => $data['frequencia_ocorrencia'] ?? null,
            'periodo_sazonal' => $data['periodo_sazonal'] ?? null,
            'areas_suscetiveis' => $data['areas_suscetiveis'] ?? null,
            'populacao_exposta' => $data['populacao_exposta'] ?? null,
            'infraestruturas_expostas' => $data['infraestruturas_expostas'] ?? null,
            'vulnerabilidades_identificadas' => $data['vulnerabilidades_identificadas'] ?? null,
            'capacidade_local_resposta' => $data['capacidade_local_resposta'] ?? null,
            'probabilidade_evento' => $data['probabilidade_evento'] ?? null,
            'impacto_potencial' => $data['impacto_potencial'] ?? null,
            'nivel_risco' => $data['nivel_risco'] ?? null,
            'fatores_agravantes' => $data['fatores_agravantes'] ?? null,
            'fatores_atenuantes' => $data['fatores_atenuantes'] ?? null,
            'fontes_informacao_utilizadas' => $data['fontes_informacao_utilizadas'] ?? null,
            'responsavel_analise' => $data['responsavel_analise'] ?? null,
            'data_analise' => $data['data_analise'] ?? null,
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createScenario(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancon_cenarios
                (
                    plancon_id, conta_id, orgao_id, unidade_id, nome_cenario, tipo_desastre_associado, descricao_cenario, evento_disparador,
                    area_afetada_estimada, populacao_potencialmente_afetada, danos_humanos_esperados, danos_materiais_esperados,
                    danos_ambientais_esperados, danos_sociais_esperados, servicos_interrompidos, tempo_evolucao_evento, necessidades_iniciais,
                    prioridades_operacionais, classificacao_cenario, observacoes_cenario, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :plancon_id, :conta_id, :orgao_id, :unidade_id, :nome_cenario, :tipo_desastre_associado, :descricao_cenario, :evento_disparador,
                    :area_afetada_estimada, :populacao_potencialmente_afetada, :danos_humanos_esperados, :danos_materiais_esperados,
                    :danos_ambientais_esperados, :danos_sociais_esperados, :servicos_interrompidos, :tempo_evolucao_evento, :necessidades_iniciais,
                    :prioridades_operacionais, :classificacao_cenario, :observacoes_cenario, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'plancon_id' => $data['plancon_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_cenario' => $data['nome_cenario'],
            'tipo_desastre_associado' => $data['tipo_desastre_associado'] ?? null,
            'descricao_cenario' => $data['descricao_cenario'],
            'evento_disparador' => $data['evento_disparador'] ?? null,
            'area_afetada_estimada' => $data['area_afetada_estimada'] ?? null,
            'populacao_potencialmente_afetada' => $data['populacao_potencialmente_afetada'] ?? null,
            'danos_humanos_esperados' => $data['danos_humanos_esperados'] ?? null,
            'danos_materiais_esperados' => $data['danos_materiais_esperados'] ?? null,
            'danos_ambientais_esperados' => $data['danos_ambientais_esperados'] ?? null,
            'danos_sociais_esperados' => $data['danos_sociais_esperados'] ?? null,
            'servicos_interrompidos' => $data['servicos_interrompidos'] ?? null,
            'tempo_evolucao_evento' => $data['tempo_evolucao_evento'] ?? null,
            'necessidades_iniciais' => $data['necessidades_iniciais'] ?? null,
            'prioridades_operacionais' => $data['prioridades_operacionais'] ?? null,
            'classificacao_cenario' => $data['classificacao_cenario'] ?? null,
            'observacoes_cenario' => $data['observacoes_cenario'] ?? null,
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createActivationLevel(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancon_niveis_ativacao
                (
                    plancon_id, conta_id, orgao_id, unidade_id, nivel_operacional, criterios_ativacao, gatilhos_acionamento,
                    autoridade_responsavel, acoes_automaticas, procedimentos_escalonamento, status_nivel, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :plancon_id, :conta_id, :orgao_id, :unidade_id, :nivel_operacional, :criterios_ativacao, :gatilhos_acionamento,
                    :autoridade_responsavel, :acoes_automaticas, :procedimentos_escalonamento, :status_nivel, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'plancon_id' => $data['plancon_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'nivel_operacional' => $data['nivel_operacional'],
            'criterios_ativacao' => $data['criterios_ativacao'] ?? null,
            'gatilhos_acionamento' => $data['gatilhos_acionamento'] ?? null,
            'autoridade_responsavel' => $data['autoridade_responsavel'] ?? null,
            'acoes_automaticas' => $data['acoes_automaticas'] ?? null,
            'procedimentos_escalonamento' => $data['procedimentos_escalonamento'] ?? null,
            'status_nivel' => $data['status_nivel'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createResource(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancon_recursos
                (
                    plancon_id, conta_id, orgao_id, unidade_id, tipo_recurso, categoria_recurso, descricao_recurso, quantidade_disponivel,
                    unidade_medida, localizacao_base, tempo_mobilizacao, status_recurso, responsavel_recurso, contato_responsavel, observacoes,
                    registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :plancon_id, :conta_id, :orgao_id, :unidade_id, :tipo_recurso, :categoria_recurso, :descricao_recurso, :quantidade_disponivel,
                    :unidade_medida, :localizacao_base, :tempo_mobilizacao, :status_recurso, :responsavel_recurso, :contato_responsavel, :observacoes,
                    :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'plancon_id' => $data['plancon_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'tipo_recurso' => $data['tipo_recurso'],
            'categoria_recurso' => $data['categoria_recurso'] ?? null,
            'descricao_recurso' => $data['descricao_recurso'],
            'quantidade_disponivel' => $data['quantidade_disponivel'] ?? null,
            'unidade_medida' => $data['unidade_medida'] ?? null,
            'localizacao_base' => $data['localizacao_base'] ?? null,
            'tempo_mobilizacao' => $data['tempo_mobilizacao'] ?? null,
            'status_recurso' => $data['status_recurso'],
            'responsavel_recurso' => $data['responsavel_recurso'] ?? null,
            'contato_responsavel' => $data['contato_responsavel'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function createReview(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plancon_revisoes
                (
                    plancon_id, conta_id, orgao_id, unidade_id, versao_revisao, motivo_revisao, alteracoes_realizadas, pendencias,
                    data_revisao, proxima_revisao, aprovado_por, status_revisao, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :plancon_id, :conta_id, :orgao_id, :unidade_id, :versao_revisao, :motivo_revisao, :alteracoes_realizadas, :pendencias,
                    :data_revisao, :proxima_revisao, :aprovado_por, :status_revisao, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'plancon_id' => $data['plancon_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'versao_revisao' => $data['versao_revisao'],
            'motivo_revisao' => $data['motivo_revisao'] ?? null,
            'alteracoes_realizadas' => $data['alteracoes_realizadas'] ?? null,
            'pendencias' => $data['pendencias'] ?? null,
            'data_revisao' => $data['data_revisao'] ?? null,
            'proxima_revisao' => $data['proxima_revisao'] ?? null,
            'aprovado_por' => $data['aprovado_por'] ?? null,
            'status_revisao' => $data['status_revisao'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
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
