<?php

declare(strict_types=1);

namespace App\Repositories\SaaS;

use App\Support\Database;
use PDO;

final class InstitutionRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function accounts(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('c', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT c.id, c.nome_fantasia, c.razao_social, c.cpf_cnpj, c.uf_sigla, c.email_principal, c.status_cadastral, c.created_at
             FROM contas c
             {$whereSql}
             ORDER BY c.id DESC
             LIMIT 150"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function accountById(int $contaId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome_fantasia, uf_sigla, status_cadastral
             FROM contas
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $contaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createAccount(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO contas (nome_fantasia, razao_social, cpf_cnpj, uf_sigla, email_principal, status_cadastral, created_at, updated_at)
             VALUES (:nome_fantasia, :razao_social, :cpf_cnpj, :uf_sigla, :email_principal, :status_cadastral, NOW(), NOW())'
        );
        $statement->execute([
            'nome_fantasia' => $data['nome_fantasia'],
            'razao_social' => $data['razao_social'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'email_principal' => $data['email_principal'] ?? null,
            'status_cadastral' => $data['status_cadastral'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function orgaos(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('o', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT o.id, o.conta_id, c.nome_fantasia AS conta_nome, o.nome_oficial, o.sigla, o.cnpj, o.uf_sigla, o.status_orgao, o.created_at
             FROM orgaos o
             INNER JOIN contas c ON c.id = o.conta_id
             {$whereSql}
             ORDER BY o.id DESC
             LIMIT 200"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function orgaoById(int $orgaoId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT o.id, o.conta_id, o.nome_oficial, o.uf_sigla, o.status_orgao
             FROM orgaos o
             WHERE o.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $orgaoId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createOrgao(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO orgaos (conta_id, nome_oficial, sigla, cnpj, uf_sigla, status_orgao, created_at, updated_at)
             VALUES (:conta_id, :nome_oficial, :sigla, :cnpj, :uf_sigla, :status_orgao, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'nome_oficial' => $data['nome_oficial'],
            'sigla' => $data['sigla'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'status_orgao' => $data['status_orgao'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function unidades(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT u.id, u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_superior_id, u.codigo_unidade, u.nome_unidade,
                    u.tipo_unidade, u.uf_sigla, u.status_unidade, u.created_at
             FROM unidades u
             INNER JOIN orgaos o ON o.id = u.orgao_id
             {$whereSql}
             ORDER BY u.id DESC
             LIMIT 250"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createUnidade(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO unidades (orgao_id, unidade_superior_id, codigo_unidade, nome_unidade, tipo_unidade, uf_sigla, status_unidade, created_at, updated_at)
             VALUES (:orgao_id, :unidade_superior_id, :codigo_unidade, :nome_unidade, :tipo_unidade, :uf_sigla, :status_unidade, NOW(), NOW())'
        );
        $statement->execute([
            'orgao_id' => $data['orgao_id'],
            'unidade_superior_id' => $data['unidade_superior_id'] ?? null,
            'codigo_unidade' => $data['codigo_unidade'] ?? null,
            'nome_unidade' => $data['nome_unidade'],
            'tipo_unidade' => $data['tipo_unidade'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'status_unidade' => $data['status_unidade'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function usuarios(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT u.id, u.nome_completo, u.email_login, u.conta_id, c.nome_fantasia AS conta_nome,
                    u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_id, un.nome_unidade AS unidade_nome,
                    u.matricula_funcional, u.uf_sigla, u.status_usuario, u.created_at
             FROM usuarios u
             INNER JOIN contas c ON c.id = u.conta_id
             INNER JOIN orgaos o ON o.id = u.orgao_id
             LEFT JOIN unidades un ON un.id = u.unidade_id
             {$whereSql}
             ORDER BY u.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createUsuario(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios
                (conta_id, orgao_id, unidade_id, uf_sigla, nome_completo, email_login, matricula_funcional, password_hash, status_usuario, created_at, updated_at)
             VALUES
                (:conta_id, :orgao_id, :unidade_id, :uf_sigla, :nome_completo, :email_login, :matricula_funcional, :password_hash, :status_usuario, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'nome_completo' => $data['nome_completo'],
            'email_login' => $data['email_login'],
            'matricula_funcional' => $data['matricula_funcional'] ?? null,
            'password_hash' => $data['password_hash'],
            'status_usuario' => $data['status_usuario'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function usuarioById(int $usuarioId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT u.id, u.conta_id, u.orgao_id, u.uf_sigla, u.status_usuario
             FROM usuarios u
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $usuarioId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function perfis(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome_perfil, descricao, status_perfil, created_at
             FROM perfis
             ORDER BY id DESC
             LIMIT 120'
        );

        return $statement->fetchAll();
    }

    public function createPerfil(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO perfis (nome_perfil, descricao, status_perfil, created_at, updated_at)
             VALUES (:nome_perfil, :descricao, :status_perfil, NOW(), NOW())'
        );
        $statement->execute([
            'nome_perfil' => $data['nome_perfil'],
            'descricao' => $data['descricao'] ?? null,
            'status_perfil' => $data['status_perfil'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function vinculosUsuarioPerfil(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT up.id, up.usuario_id, u.nome_completo AS usuario_nome, up.perfil_id, p.nome_perfil, up.created_at
             FROM usuarios_perfis up
             INNER JOIN usuarios u ON u.id = up.usuario_id
             INNER JOIN perfis p ON p.id = up.perfil_id
             {$whereSql}
             ORDER BY up.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function vincularPerfilAoUsuario(int $usuarioId, int $perfilId): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios_perfis (usuario_id, perfil_id, created_at)
             VALUES (:usuario_id, :perfil_id, NOW())
             ON DUPLICATE KEY UPDATE perfil_id = VALUES(perfil_id)'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
        ]);
    }

    public function contextOptions(?string $ufSigla = null): array
    {
        $where = [];
        $params = [];
        $this->applyUfWhere('c', $ufSigla, $where, $params);
        $accountsWhere = $this->whereClause($where);

        $whereOrgaos = [];
        $paramsOrgaos = [];
        $this->applyUfWhere('o', $ufSigla, $whereOrgaos, $paramsOrgaos);
        $orgaosWhere = $this->whereClause($whereOrgaos);

        $whereUnidades = [];
        $paramsUnidades = [];
        $this->applyUfWhere('u', $ufSigla, $whereUnidades, $paramsUnidades);
        $unidadesWhere = $this->whereClause($whereUnidades);

        $whereUsuarios = [];
        $paramsUsuarios = [];
        $this->applyUfWhere('u', $ufSigla, $whereUsuarios, $paramsUsuarios);
        $usuariosWhere = $this->whereClause($whereUsuarios);

        $ufs = [];
        if ($this->tableExists('territorios_ufs')) {
            $ufs = $this->pdo()->query(
                'SELECT sigla, nome
                 FROM territorios_ufs
                 ORDER BY nome ASC'
            )->fetchAll();
        }

        $contasStatement = $this->pdo()->prepare(
            "SELECT c.id, c.nome_fantasia, c.uf_sigla, c.status_cadastral
             FROM contas c
             {$accountsWhere}
             ORDER BY c.nome_fantasia ASC"
        );
        $contasStatement->execute($params);

        $orgaosStatement = $this->pdo()->prepare(
            "SELECT o.id, o.conta_id, o.nome_oficial, o.uf_sigla, o.status_orgao
             FROM orgaos o
             {$orgaosWhere}
             ORDER BY o.nome_oficial ASC"
        );
        $orgaosStatement->execute($paramsOrgaos);

        $unidadesStatement = $this->pdo()->prepare(
            "SELECT u.id, u.orgao_id, u.nome_unidade, u.uf_sigla, u.status_unidade
             FROM unidades u
             {$unidadesWhere}
             ORDER BY u.nome_unidade ASC"
        );
        $unidadesStatement->execute($paramsUnidades);

        $usuariosStatement = $this->pdo()->prepare(
            "SELECT u.id, u.nome_completo, u.email_login, u.uf_sigla, u.status_usuario
             FROM usuarios u
             {$usuariosWhere}
             ORDER BY u.nome_completo ASC"
        );
        $usuariosStatement->execute($paramsUsuarios);

        return [
            'ufs' => $ufs,
            'contas' => $contasStatement->fetchAll(),
            'orgaos' => $orgaosStatement->fetchAll(),
            'unidades' => $unidadesStatement->fetchAll(),
            'usuarios' => $usuariosStatement->fetchAll(),
            'perfis' => $this->pdo()->query(
                'SELECT id, nome_perfil, status_perfil FROM perfis ORDER BY nome_perfil ASC'
            )->fetchAll(),
        ];
    }

    private function applyUfWhere(string $alias, ?string $ufSigla, array &$where, array &$params): void
    {
        $ufSigla = $this->sanitizeUf($ufSigla);
        if ($ufSigla === null) {
            return;
        }

        $where[] = "{$alias}.uf_sigla = :uf_sigla";
        $params['uf_sigla'] = $ufSigla;
    }

    private function whereClause(array $where): string
    {
        if ($where === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $where);
    }

    private function sanitizeUf(?string $ufSigla): ?string
    {
        $uf = strtoupper(trim((string) $ufSigla));
        return strlen($uf) === 2 ? $uf : null;
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
