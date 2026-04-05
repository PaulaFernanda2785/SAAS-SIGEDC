<?php

declare(strict_types=1);

namespace App\Repositories\Territory;

use App\Support\Database;
use PDO;

final class TerritoryRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function ufs(): array
    {
        if (!$this->tableExists('territorios_ufs')) {
            return [];
        }

        $statement = $this->pdo()->query(
            'SELECT sigla, nome, codigo_ibge, regiao_nome
             FROM territorios_ufs
             ORDER BY nome ASC'
        );

        return $statement->fetchAll();
    }

    public function ufExists(string $ufSigla): bool
    {
        if (!$this->tableExists('territorios_ufs')) {
            return false;
        }

        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM territorios_ufs
             WHERE sigla = :uf
             LIMIT 1'
        );
        $statement->execute(['uf' => strtoupper($ufSigla)]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    public function municipiosByUf(string $ufSigla, ?string $query = null, int $limit = 150): array
    {
        if (!$this->tableExists('territorios_municipios')) {
            return [];
        }

        $ufSigla = strtoupper($ufSigla);
        $where = ['m.uf_sigla = :uf_sigla'];
        $params = ['uf_sigla' => $ufSigla];

        $query = trim((string) $query);
        if ($query !== '') {
            $where[] = 'm.nome_municipio LIKE :query';
            $params['query'] = $query . '%';
        }

        $limit = $this->normalizeLimit($limit, 150, 500);
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $statement = $this->pdo()->prepare(
            "SELECT m.codigo_ibge, m.nome_municipio, m.latitude, m.longitude
             FROM territorios_municipios m
             {$whereSql}
             ORDER BY m.nome_municipio ASC
             LIMIT {$limit}"
        );
        $statement->execute($params);

        return $statement->fetchAll();
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

    private function normalizeLimit(int $limit, int $default, int $max): int
    {
        if ($limit < 1) {
            return $default;
        }

        return min($limit, $max);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
