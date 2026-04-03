<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Support\Database;
use PDO;

final class SessionRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function create(array $sessionData): int
    {
        $sql = 'INSERT INTO sessoes_usuario
                (usuario_id, session_id_hash, ip_address, user_agent, iniciada_em, expira_em, ultimo_acesso_em, status_sessao)
                VALUES
                (:usuario_id, :session_id_hash, :ip_address, :user_agent, NOW(), :expira_em, NOW(), :status_sessao)';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute([
            'usuario_id' => $sessionData['usuario_id'],
            'session_id_hash' => $sessionData['session_id_hash'],
            'ip_address' => $sessionData['ip_address'],
            'user_agent' => $sessionData['user_agent'],
            'expira_em' => $sessionData['expira_em'],
            'status_sessao' => $sessionData['status_sessao'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function touch(int $sessionId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE sessoes_usuario SET ultimo_acesso_em = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $sessionId]);
    }

    public function close(int $sessionId, string $status = 'ENCERRADA'): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE sessoes_usuario
             SET status_sessao = :status_sessao, encerrada_em = NOW(), ultimo_acesso_em = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'status_sessao' => $status,
            'id' => $sessionId,
        ]);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}

