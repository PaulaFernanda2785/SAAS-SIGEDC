<?php

declare(strict_types=1);

namespace App\Support;

use DateTimeImmutable;

final class Logger
{
    public static function info(string $channel, string $message, array $context = []): void
    {
        self::write('INFO', $channel, $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::write('WARNING', $channel, $message, $context);
    }

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::write('ERROR', $channel, $message, $context);
    }

    private static function write(string $level, string $channel, string $message, array $context): void
    {
        $date = new DateTimeImmutable();
        $directory = storage_path('logs' . DIRECTORY_SEPARATOR . $channel);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $date->format('Y-m-d') . '.log';
        $payload = [
            'timestamp' => $date->format(DateTimeImmutable::ATOM),
            'level' => $level,
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
        ];

        file_put_contents($filePath, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }
}

