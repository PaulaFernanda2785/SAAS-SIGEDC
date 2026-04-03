<?php

declare(strict_types=1);

namespace App\Repositories\Audit;

use App\Support\Database;
use PDO;

final class AuditLogRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function record(array $data): void
    {
        $sql = 'INSERT INTO logs_auditoria
                (conta_id, orgao_id, unidade_id, usuario_id, modulo_codigo, acao, resultado, entidade_tipo, entidade_id, detalhes_json, ip_address, user_agent, created_at)
                VALUES
                (:conta_id, :orgao_id, :unidade_id, :usuario_id, :modulo_codigo, :acao, :resultado, :entidade_tipo, :entidade_id, :detalhes_json, :ip_address, :user_agent, NOW())';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute([
            'conta_id' => $data['conta_id'] ?? null,
            'orgao_id' => $data['orgao_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'usuario_id' => $data['usuario_id'] ?? null,
            'modulo_codigo' => $data['modulo_codigo'] ?? config('audit.default_module', 'AUTH'),
            'acao' => $data['acao'],
            'resultado' => $data['resultado'],
            'entidade_tipo' => $data['entidade_tipo'] ?? null,
            'entidade_id' => $data['entidade_id'] ?? null,
            'detalhes_json' => json_encode($data['detalhes'] ?? [], JSON_UNESCAPED_UNICODE),
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}

