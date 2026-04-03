<?php

declare(strict_types=1);

namespace App\Repositories\Audit;

use App\Support\Database;
use PDO;

final class AccessLogRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function record(array $data): void
    {
        $sql = 'INSERT INTO logs_acesso
                (usuario_id, conta_id, orgao_id, evento, resultado, motivo, ip_address, user_agent, created_at)
                VALUES
                (:usuario_id, :conta_id, :orgao_id, :evento, :resultado, :motivo, :ip_address, :user_agent, NOW())';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'conta_id' => $data['conta_id'] ?? null,
            'orgao_id' => $data['orgao_id'] ?? null,
            'evento' => $data['evento'],
            'resultado' => $data['resultado'],
            'motivo' => $data['motivo'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}

