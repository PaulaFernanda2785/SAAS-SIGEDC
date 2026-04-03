<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) config('database.connection', 'mysql');
        if ($driver !== 'mysql') {
            throw new RuntimeException('Apenas driver mysql esta habilitado na fase 0.');
        }

        $host = (string) config('database.host', '127.0.0.1');
        $port = (string) config('database.port', '3306');
        $database = (string) config('database.database', '');
        $charset = (string) config('database.charset', 'utf8mb4');
        $username = (string) config('database.username', '');
        $password = (string) config('database.password', '');

        if ($database === '') {
            throw new RuntimeException('DB_DATABASE nao foi configurado.');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $database, $charset);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            Logger::error('app', 'Falha na conexao com banco', [
                'error' => $exception->getMessage(),
                'host' => $host,
                'port' => $port,
                'database' => $database,
            ]);

            throw new RuntimeException('Falha ao conectar com banco de dados.');
        }

        return self::$connection;
    }
}

