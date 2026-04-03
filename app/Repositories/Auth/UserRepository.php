<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Support\Database;
use PDO;

final class UserRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function findByLogin(string $login): ?array
    {
        $sql = 'SELECT id, conta_id, orgao_id, unidade_id, nome_completo, email_login, password_hash, status_usuario
                FROM usuarios
                WHERE email_login = :login
                LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['login' => $login]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function findById(int $userId): ?array
    {
        $sql = 'SELECT id, conta_id, orgao_id, unidade_id, nome_completo, email_login, status_usuario
                FROM usuarios
                WHERE id = :id
                LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function profileCodes(int $userId): array
    {
        $sql = 'SELECT p.nome_perfil
                FROM usuarios_perfis up
                INNER JOIN perfis p ON p.id = up.perfil_id
                WHERE up.usuario_id = :usuario_id';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['usuario_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn(array $row): string => (string) $row['nome_perfil'], $rows);
    }

    public function touchLastAccess(int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE usuarios SET ultimo_acesso_em = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $userId]);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}

