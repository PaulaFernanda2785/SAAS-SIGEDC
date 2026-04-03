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
        $sql = 'SELECT u.id, u.conta_id, u.orgao_id, u.unidade_id, u.nome_completo, u.email_login, u.password_hash, u.status_usuario,
                       c.status_cadastral AS status_conta, o.status_orgao
                FROM usuarios u
                INNER JOIN contas c ON c.id = u.conta_id
                INNER JOIN orgaos o ON o.id = u.orgao_id
                WHERE u.email_login = :login
                LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['login' => $login]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function findById(int $userId): ?array
    {
        $sql = 'SELECT u.id, u.conta_id, u.orgao_id, u.unidade_id, u.nome_completo, u.email_login, u.status_usuario,
                       c.status_cadastral AS status_conta, o.status_orgao
                FROM usuarios u
                INNER JOIN contas c ON c.id = u.conta_id
                INNER JOIN orgaos o ON o.id = u.orgao_id
                WHERE u.id = :id
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
                WHERE up.usuario_id = :usuario_id
                  AND p.status_perfil = \'ATIVO\'';

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

    public function updatePasswordById(int $userId, string $passwordHash): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE usuarios
             SET password_hash = :password_hash, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'id' => $userId,
        ]);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
