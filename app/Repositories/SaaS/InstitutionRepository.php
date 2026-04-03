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

    public function accounts(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome_fantasia, razao_social, cpf_cnpj, email_principal, status_cadastral, created_at
             FROM contas
             ORDER BY id DESC
             LIMIT 150'
        );

        return $statement->fetchAll();
    }

    public function createAccount(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO contas (nome_fantasia, razao_social, cpf_cnpj, email_principal, status_cadastral, created_at, updated_at)
             VALUES (:nome_fantasia, :razao_social, :cpf_cnpj, :email_principal, :status_cadastral, NOW(), NOW())'
        );
        $statement->execute([
            'nome_fantasia' => $data['nome_fantasia'],
            'razao_social' => $data['razao_social'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'email_principal' => $data['email_principal'] ?? null,
            'status_cadastral' => $data['status_cadastral'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function orgaos(): array
    {
        $statement = $this->pdo()->query(
            'SELECT o.id, o.conta_id, c.nome_fantasia AS conta_nome, o.nome_oficial, o.sigla, o.cnpj, o.status_orgao, o.created_at
             FROM orgaos o
             INNER JOIN contas c ON c.id = o.conta_id
             ORDER BY o.id DESC
             LIMIT 200'
        );

        return $statement->fetchAll();
    }

    public function createOrgao(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO orgaos (conta_id, nome_oficial, sigla, cnpj, status_orgao, created_at, updated_at)
             VALUES (:conta_id, :nome_oficial, :sigla, :cnpj, :status_orgao, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'nome_oficial' => $data['nome_oficial'],
            'sigla' => $data['sigla'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'status_orgao' => $data['status_orgao'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function unidades(): array
    {
        $statement = $this->pdo()->query(
            'SELECT u.id, u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_superior_id, u.codigo_unidade, u.nome_unidade, u.tipo_unidade, u.status_unidade, u.created_at
             FROM unidades u
             INNER JOIN orgaos o ON o.id = u.orgao_id
             ORDER BY u.id DESC
             LIMIT 250'
        );

        return $statement->fetchAll();
    }

    public function createUnidade(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO unidades (orgao_id, unidade_superior_id, codigo_unidade, nome_unidade, tipo_unidade, status_unidade, created_at, updated_at)
             VALUES (:orgao_id, :unidade_superior_id, :codigo_unidade, :nome_unidade, :tipo_unidade, :status_unidade, NOW(), NOW())'
        );
        $statement->execute([
            'orgao_id' => $data['orgao_id'],
            'unidade_superior_id' => $data['unidade_superior_id'] ?? null,
            'codigo_unidade' => $data['codigo_unidade'] ?? null,
            'nome_unidade' => $data['nome_unidade'],
            'tipo_unidade' => $data['tipo_unidade'] ?? null,
            'status_unidade' => $data['status_unidade'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function usuarios(): array
    {
        $statement = $this->pdo()->query(
            'SELECT u.id, u.nome_completo, u.email_login, u.conta_id, c.nome_fantasia AS conta_nome,
                    u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_id, un.nome_unidade AS unidade_nome,
                    u.matricula_funcional, u.status_usuario, u.created_at
             FROM usuarios u
             INNER JOIN contas c ON c.id = u.conta_id
             INNER JOIN orgaos o ON o.id = u.orgao_id
             LEFT JOIN unidades un ON un.id = u.unidade_id
             ORDER BY u.id DESC
             LIMIT 300'
        );

        return $statement->fetchAll();
    }

    public function createUsuario(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios
                (conta_id, orgao_id, unidade_id, nome_completo, email_login, matricula_funcional, password_hash, status_usuario, created_at, updated_at)
             VALUES
                (:conta_id, :orgao_id, :unidade_id, :nome_completo, :email_login, :matricula_funcional, :password_hash, :status_usuario, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_completo' => $data['nome_completo'],
            'email_login' => $data['email_login'],
            'matricula_funcional' => $data['matricula_funcional'] ?? null,
            'password_hash' => $data['password_hash'],
            'status_usuario' => $data['status_usuario'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
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

    public function vinculosUsuarioPerfil(): array
    {
        $statement = $this->pdo()->query(
            'SELECT up.id, up.usuario_id, u.nome_completo AS usuario_nome, up.perfil_id, p.nome_perfil, up.created_at
             FROM usuarios_perfis up
             INNER JOIN usuarios u ON u.id = up.usuario_id
             INNER JOIN perfis p ON p.id = up.perfil_id
             ORDER BY up.id DESC
             LIMIT 300'
        );

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

    public function contextOptions(): array
    {
        return [
            'contas' => $this->pdo()->query(
                'SELECT id, nome_fantasia, status_cadastral FROM contas ORDER BY nome_fantasia ASC'
            )->fetchAll(),
            'orgaos' => $this->pdo()->query(
                'SELECT id, conta_id, nome_oficial, status_orgao FROM orgaos ORDER BY nome_oficial ASC'
            )->fetchAll(),
            'unidades' => $this->pdo()->query(
                'SELECT id, orgao_id, nome_unidade, status_unidade FROM unidades ORDER BY nome_unidade ASC'
            )->fetchAll(),
            'usuarios' => $this->pdo()->query(
                'SELECT id, nome_completo, email_login, status_usuario FROM usuarios ORDER BY nome_completo ASC'
            )->fetchAll(),
            'perfis' => $this->pdo()->query(
                'SELECT id, nome_perfil, status_perfil FROM perfis ORDER BY nome_perfil ASC'
            )->fetchAll(),
        ];
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
