<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Support\Database;
use PDO;

final class PasswordResetRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function invalidateOpenTokensForEmail(string $emailLogin): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE password_resets
             SET consumido_em = NOW()
             WHERE email_login = :email_login
               AND consumido_em IS NULL'
        );
        $statement->execute(['email_login' => $emailLogin]);
    }

    public function create(array $data): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO password_resets
                (usuario_id, email_login, token_hash, expira_em, solicitado_ip, user_agent, created_at)
             VALUES
                (:usuario_id, :email_login, :token_hash, :expira_em, :solicitado_ip, :user_agent, NOW())'
        );
        $statement->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'email_login' => $data['email_login'],
            'token_hash' => $data['token_hash'],
            'expira_em' => $data['expira_em'],
            'solicitado_ip' => $data['solicitado_ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    public function findOpenByTokenHash(string $tokenHash): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, usuario_id, email_login, expira_em, consumido_em
             FROM password_resets
             WHERE token_hash = :token_hash
               AND consumido_em IS NULL
             LIMIT 1'
        );
        $statement->execute(['token_hash' => $tokenHash]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function consume(int $id): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE password_resets
             SET consumido_em = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public function tableExists(): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name'
        );
        $statement->execute(['table_name' => 'password_resets']);

        return ((int) $statement->fetchColumn()) > 0;
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
