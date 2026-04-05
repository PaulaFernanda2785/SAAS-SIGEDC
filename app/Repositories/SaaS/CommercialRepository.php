<?php

declare(strict_types=1);

namespace App\Repositories\SaaS;

use App\Support\Database;
use PDO;

final class CommercialRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function plans(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios, status_plano, created_at
             FROM planos_catalogo
             ORDER BY id DESC
             LIMIT 120'
        );

        return $statement->fetchAll();
    }

    public function activePlansForPublicPage(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios
             FROM planos_catalogo
             WHERE status_plano = \'ATIVO\'
             ORDER BY preco_mensal ASC, nome_plano ASC'
        );

        return $statement->fetchAll();
    }

    public function createPlan(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO planos_catalogo
                (codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios, status_plano, created_at, updated_at)
             VALUES
                (:codigo_plano, :nome_plano, :descricao, :preco_mensal, :limite_usuarios, :status_plano, NOW(), NOW())'
        );
        $statement->execute([
            'codigo_plano' => $data['codigo_plano'],
            'nome_plano' => $data['nome_plano'],
            'descricao' => $data['descricao'] ?? null,
            'preco_mensal' => $data['preco_mensal'],
            'limite_usuarios' => $data['limite_usuarios'] ?? null,
            'status_plano' => $data['status_plano'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function assinaturas(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('a', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT a.id, a.conta_id, c.nome_fantasia AS conta_nome, a.uf_sigla,
                    a.plano_id, p.nome_plano, a.status_assinatura,
                    a.inicia_em, a.expira_em, a.trial_fim_em, a.motivo_status, a.created_at
             FROM assinaturas a
             INNER JOIN contas c ON c.id = a.conta_id
             INNER JOIN planos_catalogo p ON p.id = a.plano_id
             {$whereSql}
             ORDER BY a.id DESC
             LIMIT 180"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function planByCode(string $codigoPlano): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios, status_plano
             FROM planos_catalogo
             WHERE UPPER(codigo_plano) = :codigo_plano
             LIMIT 1'
        );
        $statement->execute([
            'codigo_plano' => strtoupper(trim($codigoPlano)),
        ]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createAssinatura(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas
                (conta_id, uf_sigla, plano_id, status_assinatura, inicia_em, expira_em, trial_fim_em, motivo_status, created_at, updated_at)
             VALUES
                (:conta_id, :uf_sigla, :plano_id, :status_assinatura, :inicia_em, :expira_em, :trial_fim_em, :motivo_status, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'uf_sigla' => $data['uf_sigla'],
            'plano_id' => $data['plano_id'],
            'status_assinatura' => $data['status_assinatura'],
            'inicia_em' => $data['inicia_em'],
            'expira_em' => $data['expira_em'] ?? null,
            'trial_fim_em' => $data['trial_fim_em'] ?? null,
            'motivo_status' => $data['motivo_status'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function assinaturaById(int $assinaturaId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, conta_id, uf_sigla, status_assinatura
             FROM assinaturas
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $assinaturaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function latestAssinaturaByConta(int $contaId): ?array
    {
        if (!$this->tableExists('assinaturas')) {
            return null;
        }

        $statement = $this->pdo()->prepare(
            'SELECT id, conta_id, uf_sigla, plano_id, status_assinatura, inicia_em, expira_em, trial_fim_em, motivo_status
             FROM assinaturas
             WHERE conta_id = :conta_id
             ORDER BY id DESC
             LIMIT 1'
        );
        $statement->execute(['conta_id' => $contaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function modules(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, codigo_modulo, nome_modulo, status_modulo
             FROM modulos
             ORDER BY nome_modulo ASC'
        );

        return $statement->fetchAll();
    }

    public function liberarModulo(int $assinaturaId, int $moduloId, string $status): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas_modulos
                (assinatura_id, modulo_id, status_liberacao, liberado_em, bloqueado_em, created_at, updated_at)
             VALUES
                (:assinatura_id, :modulo_id, :status_liberacao, NOW(), :bloqueado_em, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                status_liberacao = VALUES(status_liberacao),
                bloqueado_em = VALUES(bloqueado_em),
                updated_at = NOW()'
        );
        $statement->execute([
            'assinatura_id' => $assinaturaId,
            'modulo_id' => $moduloId,
            'status_liberacao' => $status,
            'bloqueado_em' => $status === 'BLOQUEADA' ? date('Y-m-d H:i:s') : null,
        ]);
    }

    public function modulosPorAssinatura(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('a', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT am.id, am.assinatura_id, a.uf_sigla, am.modulo_id, m.codigo_modulo, m.nome_modulo, am.status_liberacao, am.updated_at
             FROM assinaturas_modulos am
             INNER JOIN assinaturas a ON a.id = am.assinatura_id
             INNER JOIN modulos m ON m.id = am.modulo_id
             {$whereSql}
             ORDER BY am.id DESC
             LIMIT 250"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function summary(): array
    {
        return [
            'contas' => (int) $this->pdo()->query('SELECT COUNT(*) FROM contas')->fetchColumn(),
            'orgaos' => (int) $this->pdo()->query('SELECT COUNT(*) FROM orgaos')->fetchColumn(),
            'unidades' => (int) $this->pdo()->query('SELECT COUNT(*) FROM unidades')->fetchColumn(),
            'usuarios' => (int) $this->pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
            'perfis' => (int) $this->pdo()->query('SELECT COUNT(*) FROM perfis')->fetchColumn(),
            'planos' => $this->tableExists('planos_catalogo')
                ? (int) $this->pdo()->query('SELECT COUNT(*) FROM planos_catalogo')->fetchColumn()
                : 0,
            'assinaturas_ativas' => $this->tableExists('assinaturas')
                ? (int) $this->pdo()->query('SELECT COUNT(*) FROM assinaturas WHERE status_assinatura IN (\'TRIAL\', \'ATIVA\')')->fetchColumn()
                : 0,
        ];
    }

    public function contractForAuth(int $contaId): ?array
    {
        if (!$this->tableExists('assinaturas') || !$this->tableExists('assinaturas_modulos')) {
            return null;
        }

        $statement = $this->pdo()->prepare(
            'SELECT a.id, a.uf_sigla, a.status_assinatura, a.inicia_em, a.expira_em, a.trial_fim_em, a.motivo_status,
                    SUM(CASE WHEN m.codigo_modulo = \'AUTH\' AND am.status_liberacao = \'ATIVA\' THEN 1 ELSE 0 END) AS auth_liberado
             FROM assinaturas a
             LEFT JOIN assinaturas_modulos am ON am.assinatura_id = a.id
             LEFT JOIN modulos m ON m.id = am.modulo_id
             WHERE a.conta_id = :conta_id
               AND a.status_assinatura IN (\'TRIAL\', \'ATIVA\')
               AND a.inicia_em <= CURDATE()
               AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
             GROUP BY a.id, a.status_assinatura, a.inicia_em, a.expira_em, a.trial_fim_em, a.motivo_status
             ORDER BY a.id DESC
             LIMIT 1'
        );
        $statement->execute(['conta_id' => $contaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function moduleCodesByAssinatura(int $assinaturaId): array
    {
        if (!$this->tableExists('assinaturas_modulos')) {
            return [];
        }

        $statement = $this->pdo()->prepare(
            'SELECT m.codigo_modulo
             FROM assinaturas_modulos am
             INNER JOIN modulos m ON m.id = am.modulo_id
             WHERE am.assinatura_id = :assinatura_id
               AND am.status_liberacao = \'ATIVA\''
        );
        $statement->execute(['assinatura_id' => $assinaturaId]);

        $rows = $statement->fetchAll();

        return array_map(static fn(array $row): string => (string) $row['codigo_modulo'], $rows);
    }

    public function moduleIdsByCodes(array $codes): array
    {
        if ($codes === []) {
            return [];
        }

        $normalizedCodes = [];
        foreach ($codes as $code) {
            $normalized = strtoupper(trim((string) $code));
            if ($normalized !== '') {
                $normalizedCodes[] = $normalized;
            }
        }

        if ($normalizedCodes === []) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach (array_values(array_unique($normalizedCodes)) as $index => $code) {
            $param = ':code_' . $index;
            $placeholders[] = $param;
            $params['code_' . $index] = $code;
        }

        $statement = $this->pdo()->prepare(
            'SELECT id, codigo_modulo
             FROM modulos
             WHERE codigo_modulo IN (' . implode(', ', $placeholders) . ')'
        );
        $statement->execute($params);

        $rows = $statement->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $code = strtoupper((string) ($row['codigo_modulo'] ?? ''));
            if ($code === '') {
                continue;
            }

            $result[$code] = (int) ($row['id'] ?? 0);
        }

        return $result;
    }

    private function applyUfWhere(string $alias, ?string $ufSigla, array &$where, array &$params): void
    {
        $uf = strtoupper(trim((string) $ufSigla));
        if (strlen($uf) !== 2) {
            return;
        }

        $where[] = "{$alias}.uf_sigla = :uf_sigla";
        $params['uf_sigla'] = $uf;
    }

    private function whereClause(array $where): string
    {
        return $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);
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
