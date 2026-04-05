<?php

declare(strict_types=1);

namespace App\Repositories\Operational;

use App\Support\Database;
use PDO;

final class DocumentRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function createAttachment(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO anexos
                (
                    conta_id, orgao_id, usuario_envio_id, entidade_tipo, entidade_id, arquivo_nome,
                    arquivo_caminho, arquivo_mime, tamanho_bytes, hash_arquivo, visibilidade, created_at
                )
             VALUES
                (
                    :conta_id, :orgao_id, :usuario_envio_id, :entidade_tipo, :entidade_id, :arquivo_nome,
                    :arquivo_caminho, :arquivo_mime, :tamanho_bytes, :hash_arquivo, :visibilidade, NOW()
                )'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'usuario_envio_id' => $data['usuario_envio_id'] ?? null,
            'entidade_tipo' => $data['entidade_tipo'],
            'entidade_id' => $data['entidade_id'],
            'arquivo_nome' => $data['arquivo_nome'],
            'arquivo_caminho' => $data['arquivo_caminho'],
            'arquivo_mime' => $data['arquivo_mime'],
            'tamanho_bytes' => $data['tamanho_bytes'],
            'hash_arquivo' => $data['hash_arquivo'] ?? null,
            'visibilidade' => $data['visibilidade'] ?? 'PRIVADO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function attachmentsByScope(array $scope, ?string $entityType, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('a', $scope, $where, $params);

        if ($entityType !== null) {
            $where[] = 'a.entidade_tipo = :entidade_tipo';
            $params['entidade_tipo'] = $entityType;
        }

        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 400);

        $statement = $this->pdo()->prepare(
            "SELECT
                a.id,
                a.entidade_tipo,
                a.entidade_id,
                a.arquivo_nome,
                a.arquivo_mime,
                a.tamanho_bytes,
                a.visibilidade,
                a.created_at,
                u.nome_completo AS enviado_por
             FROM anexos a
             LEFT JOIN usuarios u ON u.id = a.usuario_envio_id
             {$whereSql}
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function attachmentsByEntityType(array $scope): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('a', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT a.entidade_tipo, COUNT(*) AS total
             FROM anexos a
             {$whereSql}
             GROUP BY a.entidade_tipo
             ORDER BY total DESC"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function incidentOptions(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('i', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT i.id, i.numero_ocorrencia, i.nome_incidente
             FROM incidentes i
             {$whereSql}
             ORDER BY i.data_hora_abertura DESC, i.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function planconOptions(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('p', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT p.id, p.titulo_plano, p.versao_documento
             FROM plancons p
             {$whereSql}
             ORDER BY p.updated_at DESC, p.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function incidentRecordOptions(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT
                r.id,
                r.incidente_id,
                r.titulo_registro,
                i.numero_ocorrencia
             FROM incidentes_registros_operacionais r
             INNER JOIN incidentes i ON i.id = r.incidente_id
             {$whereSql}
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function planconRiskOptions(array $scope, int $limit = 120): array
    {
        $where = [];
        $params = [];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);
        $limit = $this->normalizeLimit($limit, 120, 300);

        $statement = $this->pdo()->prepare(
            "SELECT
                r.id,
                r.plancon_id,
                LEFT(r.descricao_risco, 120) AS descricao_curta,
                p.titulo_plano
             FROM plancon_riscos r
             INNER JOIN plancons p ON p.id = r.plancon_id
             {$whereSql}
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function existsIncidentRecordInScope(array $scope, int $recordId): bool
    {
        $where = ['r.id = :record_id'];
        $params = ['record_id' => $recordId];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM incidentes_registros_operacionais r
             {$whereSql}
             LIMIT 1"
        );
        $statement->execute($params);

        return ((int) $statement->fetchColumn()) > 0;
    }

    public function existsPlanconRiskInScope(array $scope, int $riskId): bool
    {
        $where = ['r.id = :risk_id'];
        $params = ['risk_id' => $riskId];
        $this->applyScopeWhere('r', $scope, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT COUNT(*)
             FROM plancon_riscos r
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
