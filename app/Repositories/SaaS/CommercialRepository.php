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

    public function assinaturas(): array
    {
        $statement = $this->pdo()->query(
            'SELECT a.id, a.conta_id, c.nome_fantasia AS conta_nome,
                    a.plano_id, p.nome_plano, a.status_assinatura,
                    a.inicia_em, a.expira_em, a.trial_fim_em, a.motivo_status, a.created_at
             FROM assinaturas a
             INNER JOIN contas c ON c.id = a.conta_id
             INNER JOIN planos_catalogo p ON p.id = a.plano_id
             ORDER BY a.id DESC
             LIMIT 180'
        );

        return $statement->fetchAll();
    }

    public function createAssinatura(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO assinaturas
                (conta_id, plano_id, status_assinatura, inicia_em, expira_em, trial_fim_em, motivo_status, created_at, updated_at)
             VALUES
                (:conta_id, :plano_id, :status_assinatura, :inicia_em, :expira_em, :trial_fim_em, :motivo_status, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'plano_id' => $data['plano_id'],
            'status_assinatura' => $data['status_assinatura'],
            'inicia_em' => $data['inicia_em'],
            'expira_em' => $data['expira_em'] ?? null,
            'trial_fim_em' => $data['trial_fim_em'] ?? null,
            'motivo_status' => $data['motivo_status'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
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

    public function modulosPorAssinatura(): array
    {
        $statement = $this->pdo()->query(
            'SELECT am.id, am.assinatura_id, am.modulo_id, m.codigo_modulo, m.nome_modulo, am.status_liberacao, am.updated_at
             FROM assinaturas_modulos am
             INNER JOIN modulos m ON m.id = am.modulo_id
             ORDER BY am.id DESC
             LIMIT 250'
        );

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
            'SELECT a.id, a.status_assinatura, a.inicia_em, a.expira_em,
                    SUM(CASE WHEN m.codigo_modulo = \'AUTH\' AND am.status_liberacao = \'ATIVA\' THEN 1 ELSE 0 END) AS auth_liberado
             FROM assinaturas a
             LEFT JOIN assinaturas_modulos am ON am.assinatura_id = a.id
             LEFT JOIN modulos m ON m.id = am.modulo_id
             WHERE a.conta_id = :conta_id
               AND a.status_assinatura IN (\'TRIAL\', \'ATIVA\')
               AND a.inicia_em <= CURDATE()
               AND (a.expira_em IS NULL OR a.expira_em >= CURDATE())
             GROUP BY a.id, a.status_assinatura, a.inicia_em, a.expira_em
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
