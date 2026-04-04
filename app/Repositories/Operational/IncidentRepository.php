<?php

declare(strict_types=1);

namespace App\Repositories\Operational;

use App\Support\Database;
use PDO;

final class IncidentRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function dashboardSummary(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $sql = "SELECT
                    SUM(CASE WHEN i.status_incidente = 'ABERTO' THEN 1 ELSE 0 END) AS incidentes_abertos,
                    SUM(CASE WHEN i.status_incidente = 'EM_ANDAMENTO' THEN 1 ELSE 0 END) AS incidentes_em_andamento,
                    SUM(CASE WHEN i.status_incidente = 'CONTROLADO' THEN 1 ELSE 0 END) AS incidentes_controlados,
                    SUM(CASE WHEN i.status_incidente = 'ENCERRADO' THEN 1 ELSE 0 END) AS incidentes_encerrados,
                    COUNT(*) AS total_incidentes
                FROM incidentes i
                {$whereSql}";
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch() ?: [];

        $sqlPeriods = "SELECT COUNT(*)
                       FROM incidentes_periodos_operacionais p
                       INNER JOIN incidentes i ON i.id = p.incidente_id
                       {$whereSql}
                         AND p.status_periodo = 'ATIVO'";
        $statementPeriods = $this->pdo()->prepare($sqlPeriods);
        $statementPeriods->execute($params);

        $sqlRecords = "SELECT COUNT(*)
                       FROM incidentes_registros_operacionais r
                       INNER JOIN incidentes i ON i.id = r.incidente_id
                       {$whereSql}
                         AND r.data_hora_registro >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $statementRecords = $this->pdo()->prepare($sqlRecords);
        $statementRecords->execute($params);

        return [
            'incidentes_abertos' => (int) ($row['incidentes_abertos'] ?? 0),
            'incidentes_em_andamento' => (int) ($row['incidentes_em_andamento'] ?? 0),
            'incidentes_controlados' => (int) ($row['incidentes_controlados'] ?? 0),
            'incidentes_encerrados' => (int) ($row['incidentes_encerrados'] ?? 0),
            'total_incidentes' => (int) ($row['total_incidentes'] ?? 0),
            'periodos_ativos' => (int) $statementPeriods->fetchColumn(),
            'registros_24h' => (int) $statementRecords->fetchColumn(),
        ];
    }

    public function recentIncidents(array $scope, int $limit = 10): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 10, 50);

        $statement = $this->pdo()->prepare(
            "SELECT i.id, i.numero_ocorrencia, i.nome_incidente, i.tipo_ocorrencia, i.status_incidente,
                    i.municipio, i.data_hora_abertura, i.created_at,
                    u.nome_completo AS aberto_por_nome
             FROM incidentes i
             INNER JOIN usuarios u ON u.id = i.aberto_por_usuario_id
             {$whereSql}
             ORDER BY i.data_hora_abertura DESC, i.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function incidentsForWorkspace(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT i.id, i.numero_ocorrencia, i.nome_incidente, i.tipo_ocorrencia, i.classificacao_inicial,
                    i.status_incidente, i.municipio, i.data_hora_abertura, i.unidade_id,
                    (SELECT MAX(b.versao_briefing) FROM incidentes_briefing b WHERE b.incidente_id = i.id) AS ultima_versao_briefing,
                    (SELECT MAX(p.numero_periodo) FROM incidentes_periodos_operacionais p WHERE p.incidente_id = i.id AND p.status_periodo = 'ATIVO') AS periodo_ativo_numero
             FROM incidentes i
             {$whereSql}
             ORDER BY i.data_hora_abertura DESC, i.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function incidentOptions(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT i.id, i.numero_ocorrencia, i.nome_incidente, i.status_incidente
             FROM incidentes i
             {$whereSql}
             ORDER BY i.data_hora_abertura DESC, i.id DESC
             LIMIT 250"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function periodOptions(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT p.id, p.incidente_id, p.numero_periodo, p.status_periodo,
                    i.numero_ocorrencia, i.nome_incidente
             FROM incidentes_periodos_operacionais p
             INNER JOIN incidentes i ON i.id = p.incidente_id
             {$whereSql}
             ORDER BY p.created_at DESC, p.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function recentRecords(array $scope, int $limit = 40): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 40, 200);

        $statement = $this->pdo()->prepare(
            "SELECT r.id, r.incidente_id, r.periodo_operacional_id, r.data_hora_registro, r.tipo_registro,
                    r.titulo_registro, r.criticidade, r.status_registro,
                    i.numero_ocorrencia, i.nome_incidente
             FROM incidentes_registros_operacionais r
             INNER JOIN incidentes i ON i.id = r.incidente_id
             {$whereSql}
             ORDER BY r.data_hora_registro DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createIncident(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes
                (
                    conta_id, orgao_id, unidade_id, numero_ocorrencia, nome_incidente, tipo_ocorrencia, classificacao_inicial,
                    data_hora_acionamento, data_hora_abertura, municipio, local_detalhado, coordenadas, orgao_primeira_informacao,
                    canal_recebimento, comunicante, descricao_inicial, situacao_inicial_observada, populacao_potencialmente_afetada,
                    danos_humanos_iniciais, danos_materiais_iniciais, danos_ambientais_iniciais, riscos_imediatos, orgao_lider_inicial,
                    status_incidente, plancon_id, cenario_id, aberto_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :unidade_id, :numero_ocorrencia, :nome_incidente, :tipo_ocorrencia, :classificacao_inicial,
                    :data_hora_acionamento, :data_hora_abertura, :municipio, :local_detalhado, :coordenadas, :orgao_primeira_informacao,
                    :canal_recebimento, :comunicante, :descricao_inicial, :situacao_inicial_observada, :populacao_potencialmente_afetada,
                    :danos_humanos_iniciais, :danos_materiais_iniciais, :danos_ambientais_iniciais, :riscos_imediatos, :orgao_lider_inicial,
                    :status_incidente, :plancon_id, :cenario_id, :aberto_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'numero_ocorrencia' => $data['numero_ocorrencia'],
            'nome_incidente' => $data['nome_incidente'],
            'tipo_ocorrencia' => $data['tipo_ocorrencia'],
            'classificacao_inicial' => $data['classificacao_inicial'] ?? null,
            'data_hora_acionamento' => $data['data_hora_acionamento'] ?? null,
            'data_hora_abertura' => $data['data_hora_abertura'],
            'municipio' => $data['municipio'] ?? null,
            'local_detalhado' => $data['local_detalhado'] ?? null,
            'coordenadas' => $data['coordenadas'] ?? null,
            'orgao_primeira_informacao' => $data['orgao_primeira_informacao'] ?? null,
            'canal_recebimento' => $data['canal_recebimento'] ?? null,
            'comunicante' => $data['comunicante'] ?? null,
            'descricao_inicial' => $data['descricao_inicial'],
            'situacao_inicial_observada' => $data['situacao_inicial_observada'] ?? null,
            'populacao_potencialmente_afetada' => $data['populacao_potencialmente_afetada'] ?? null,
            'danos_humanos_iniciais' => $data['danos_humanos_iniciais'] ?? null,
            'danos_materiais_iniciais' => $data['danos_materiais_iniciais'] ?? null,
            'danos_ambientais_iniciais' => $data['danos_ambientais_iniciais'] ?? null,
            'riscos_imediatos' => $data['riscos_imediatos'] ?? null,
            'orgao_lider_inicial' => $data['orgao_lider_inicial'] ?? null,
            'status_incidente' => $data['status_incidente'],
            'plancon_id' => $data['plancon_id'] ?? null,
            'cenario_id' => $data['cenario_id'] ?? null,
            'aberto_por_usuario_id' => $data['aberto_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function findIncidentById(array $scope, int $incidentId): ?array
    {
        $where = ['i.id = :incidente_id'];
        $params = ['incidente_id' => $incidentId];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT i.*
             FROM incidentes i
             {$whereSql}
             LIMIT 1"
        );
        $statement->execute($params);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function nextBriefingVersion(int $incidentId): int
    {
        $statement = $this->pdo()->prepare(
            'SELECT COALESCE(MAX(versao_briefing), 0) + 1
             FROM incidentes_briefing
             WHERE incidente_id = :incidente_id'
        );
        $statement->execute(['incidente_id' => $incidentId]);

        return (int) $statement->fetchColumn();
    }

    public function createBriefing(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_briefing
                (
                    incidente_id, conta_id, orgao_id, unidade_id, versao_briefing, resumo_situacao, eventos_significativos,
                    objetivos_iniciais, acoes_atuais, recursos_alocados, recursos_solicitados, riscos_criticos_seguranca,
                    restricoes_operacionais, necessidades_imediatas, responsavel_briefing, data_hora_briefing,
                    uso_transferencia_comando, observacoes, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :conta_id, :orgao_id, :unidade_id, :versao_briefing, :resumo_situacao, :eventos_significativos,
                    :objetivos_iniciais, :acoes_atuais, :recursos_alocados, :recursos_solicitados, :riscos_criticos_seguranca,
                    :restricoes_operacionais, :necessidades_imediatas, :responsavel_briefing, :data_hora_briefing,
                    :uso_transferencia_comando, :observacoes, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'versao_briefing' => $data['versao_briefing'],
            'resumo_situacao' => $data['resumo_situacao'],
            'eventos_significativos' => $data['eventos_significativos'] ?? null,
            'objetivos_iniciais' => $data['objetivos_iniciais'] ?? null,
            'acoes_atuais' => $data['acoes_atuais'] ?? null,
            'recursos_alocados' => $data['recursos_alocados'] ?? null,
            'recursos_solicitados' => $data['recursos_solicitados'] ?? null,
            'riscos_criticos_seguranca' => $data['riscos_criticos_seguranca'] ?? null,
            'restricoes_operacionais' => $data['restricoes_operacionais'] ?? null,
            'necessidades_imediatas' => $data['necessidades_imediatas'] ?? null,
            'responsavel_briefing' => $data['responsavel_briefing'] ?? null,
            'data_hora_briefing' => $data['data_hora_briefing'] ?? null,
            'uso_transferencia_comando' => $data['uso_transferencia_comando'] ?? 0,
            'observacoes' => $data['observacoes'] ?? null,
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function upsertCommand(array $data): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_comando
                (
                    incidente_id, conta_id, orgao_id, unidade_id, tipo_comando, comandante_usuario_id, comandante_nome,
                    instituicao_comandante, autoridade_legal, comando_unificado, data_hora_assuncao, data_hora_transferencia,
                    motivo_transferencia, base_legal_ativacao, local_posto_comando, status_comando, diretrizes_institucionais,
                    restricoes_juridicas_operacionais, observacoes, atualizado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :conta_id, :orgao_id, :unidade_id, :tipo_comando, :comandante_usuario_id, :comandante_nome,
                    :instituicao_comandante, :autoridade_legal, :comando_unificado, :data_hora_assuncao, :data_hora_transferencia,
                    :motivo_transferencia, :base_legal_ativacao, :local_posto_comando, :status_comando, :diretrizes_institucionais,
                    :restricoes_juridicas_operacionais, :observacoes, :atualizado_por_usuario_id, NOW(), NOW()
                )
             ON DUPLICATE KEY UPDATE
                unidade_id = VALUES(unidade_id),
                tipo_comando = VALUES(tipo_comando),
                comandante_usuario_id = VALUES(comandante_usuario_id),
                comandante_nome = VALUES(comandante_nome),
                instituicao_comandante = VALUES(instituicao_comandante),
                autoridade_legal = VALUES(autoridade_legal),
                comando_unificado = VALUES(comando_unificado),
                data_hora_assuncao = VALUES(data_hora_assuncao),
                data_hora_transferencia = VALUES(data_hora_transferencia),
                motivo_transferencia = VALUES(motivo_transferencia),
                base_legal_ativacao = VALUES(base_legal_ativacao),
                local_posto_comando = VALUES(local_posto_comando),
                status_comando = VALUES(status_comando),
                diretrizes_institucionais = VALUES(diretrizes_institucionais),
                restricoes_juridicas_operacionais = VALUES(restricoes_juridicas_operacionais),
                observacoes = VALUES(observacoes),
                atualizado_por_usuario_id = VALUES(atualizado_por_usuario_id),
                updated_at = NOW()'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'tipo_comando' => $data['tipo_comando'],
            'comandante_usuario_id' => $data['comandante_usuario_id'] ?? null,
            'comandante_nome' => $data['comandante_nome'] ?? null,
            'instituicao_comandante' => $data['instituicao_comandante'] ?? null,
            'autoridade_legal' => $data['autoridade_legal'] ?? null,
            'comando_unificado' => $data['comando_unificado'] ?? null,
            'data_hora_assuncao' => $data['data_hora_assuncao'] ?? null,
            'data_hora_transferencia' => $data['data_hora_transferencia'] ?? null,
            'motivo_transferencia' => $data['motivo_transferencia'] ?? null,
            'base_legal_ativacao' => $data['base_legal_ativacao'] ?? null,
            'local_posto_comando' => $data['local_posto_comando'] ?? null,
            'status_comando' => $data['status_comando'],
            'diretrizes_institucionais' => $data['diretrizes_institucionais'] ?? null,
            'restricoes_juridicas_operacionais' => $data['restricoes_juridicas_operacionais'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
            'atualizado_por_usuario_id' => $data['atualizado_por_usuario_id'],
        ]);
    }

    public function nextPeriodNumber(int $incidentId): int
    {
        $statement = $this->pdo()->prepare(
            'SELECT COALESCE(MAX(numero_periodo), 0) + 1
             FROM incidentes_periodos_operacionais
             WHERE incidente_id = :incidente_id'
        );
        $statement->execute(['incidente_id' => $incidentId]);

        return (int) $statement->fetchColumn();
    }

    public function createPeriod(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_periodos_operacionais
                (
                    incidente_id, conta_id, orgao_id, unidade_id, numero_periodo, data_hora_inicio, data_hora_fim,
                    situacao_inicial_periodo, objetivos_periodo, recursos_principais_periodo, briefing_realizado, pai_vinculado,
                    situacao_encerramento, pendencias, responsavel_aprovacao, status_periodo, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :conta_id, :orgao_id, :unidade_id, :numero_periodo, :data_hora_inicio, :data_hora_fim,
                    :situacao_inicial_periodo, :objetivos_periodo, :recursos_principais_periodo, :briefing_realizado, :pai_vinculado,
                    :situacao_encerramento, :pendencias, :responsavel_aprovacao, :status_periodo, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'numero_periodo' => $data['numero_periodo'],
            'data_hora_inicio' => $data['data_hora_inicio'],
            'data_hora_fim' => $data['data_hora_fim'] ?? null,
            'situacao_inicial_periodo' => $data['situacao_inicial_periodo'] ?? null,
            'objetivos_periodo' => $data['objetivos_periodo'] ?? null,
            'recursos_principais_periodo' => $data['recursos_principais_periodo'] ?? null,
            'briefing_realizado' => $data['briefing_realizado'] ?? 0,
            'pai_vinculado' => $data['pai_vinculado'] ?? null,
            'situacao_encerramento' => $data['situacao_encerramento'] ?? null,
            'pendencias' => $data['pendencias'] ?? null,
            'responsavel_aprovacao' => $data['responsavel_aprovacao'] ?? null,
            'status_periodo' => $data['status_periodo'],
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function periodBelongsToIncident(array $scope, int $periodId, int $incidentId): bool
    {
        $where = [
            'p.id = :periodo_id',
            'p.incidente_id = :incidente_id',
        ];
        $params = [
            'periodo_id' => $periodId,
            'incidente_id' => $incidentId,
        ];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM incidentes_periodos_operacionais p
             INNER JOIN incidentes i ON i.id = p.incidente_id
             {$whereSql}"
        );
        $statement->execute($params);

        return ((int) $statement->fetchColumn()) > 0;
    }

    public function createRecord(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO incidentes_registros_operacionais
                (
                    incidente_id, periodo_operacional_id, conta_id, orgao_id, unidade_id, data_hora_registro, tipo_registro,
                    titulo_registro, descricao_registro, origem_informacao, responsavel_lancamento, encaminhamento,
                    status_registro, criticidade, dados_json, registrado_por_usuario_id, created_at, updated_at
                )
             VALUES
                (
                    :incidente_id, :periodo_operacional_id, :conta_id, :orgao_id, :unidade_id, :data_hora_registro, :tipo_registro,
                    :titulo_registro, :descricao_registro, :origem_informacao, :responsavel_lancamento, :encaminhamento,
                    :status_registro, :criticidade, :dados_json, :registrado_por_usuario_id, NOW(), NOW()
                )'
        );
        $statement->execute([
            'incidente_id' => $data['incidente_id'],
            'periodo_operacional_id' => $data['periodo_operacional_id'] ?? null,
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'data_hora_registro' => $data['data_hora_registro'],
            'tipo_registro' => $data['tipo_registro'],
            'titulo_registro' => $data['titulo_registro'],
            'descricao_registro' => $data['descricao_registro'],
            'origem_informacao' => $data['origem_informacao'] ?? null,
            'responsavel_lancamento' => $data['responsavel_lancamento'] ?? null,
            'encaminhamento' => $data['encaminhamento'] ?? null,
            'status_registro' => $data['status_registro'],
            'criticidade' => $data['criticidade'],
            'dados_json' => $data['dados_json'] ?? null,
            'registrado_por_usuario_id' => $data['registrado_por_usuario_id'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function reportStatusSummary(array $scope, ?string $dateFrom, ?string $dateTo): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        if ($dateFrom !== null) {
            $where[] = 'i.data_hora_abertura >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== null) {
            $where[] = 'i.data_hora_abertura <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
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

    public function reportRecordsByType(array $scope, ?string $dateFrom, ?string $dateTo, ?int $incidentId): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        if ($dateFrom !== null) {
            $where[] = 'r.data_hora_registro >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== null) {
            $where[] = 'r.data_hora_registro <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
        if (($incidentId ?? 0) > 0) {
            $where[] = 'r.incidente_id = :incidente_id';
            $params['incidente_id'] = $incidentId;
        }
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT r.tipo_registro, COUNT(*) AS total
             FROM incidentes_registros_operacionais r
             INNER JOIN incidentes i ON i.id = r.incidente_id
             {$whereSql}
             GROUP BY r.tipo_registro
             ORDER BY total DESC"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function reportIncidents(
        array $scope,
        ?string $status,
        ?string $dateFrom,
        ?string $dateTo,
        int $limit = 150
    ): array {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        if ($status !== null) {
            $where[] = 'i.status_incidente = :status_incidente';
            $params['status_incidente'] = $status;
        }
        if ($dateFrom !== null) {
            $where[] = 'i.data_hora_abertura >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== null) {
            $where[] = 'i.data_hora_abertura <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 150, 400);

        $statement = $this->pdo()->prepare(
            "SELECT i.id, i.numero_ocorrencia, i.nome_incidente, i.tipo_ocorrencia, i.classificacao_inicial,
                    i.status_incidente, i.municipio, i.data_hora_abertura,
                    (SELECT COUNT(*) FROM incidentes_registros_operacionais r WHERE r.incidente_id = i.id) AS total_registros,
                    (SELECT MAX(p.numero_periodo) FROM incidentes_periodos_operacionais p WHERE p.incidente_id = i.id) AS ultimo_periodo
             FROM incidentes i
             {$whereSql}
             ORDER BY i.data_hora_abertura DESC, i.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function reportRecentRecords(
        array $scope,
        ?string $dateFrom,
        ?string $dateTo,
        ?int $incidentId,
        int $limit = 120
    ): array {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        if ($dateFrom !== null) {
            $where[] = 'r.data_hora_registro >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo !== null) {
            $where[] = 'r.data_hora_registro <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }
        if (($incidentId ?? 0) > 0) {
            $where[] = 'r.incidente_id = :incidente_id';
            $params['incidente_id'] = $incidentId;
        }
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT r.id, r.incidente_id, r.periodo_operacional_id, r.data_hora_registro, r.tipo_registro,
                    r.titulo_registro, r.status_registro, r.criticidade,
                    i.numero_ocorrencia, i.nome_incidente
             FROM incidentes_registros_operacionais r
             INNER JOIN incidentes i ON i.id = r.incidente_id
             {$whereSql}
             ORDER BY r.data_hora_registro DESC, r.id DESC
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
        if ($where === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $where);
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
