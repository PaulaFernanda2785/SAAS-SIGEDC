<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public static function token(string $key = 'default'): string
    {
        if (!isset($_SESSION['_csrf'][$key])) {
            $_SESSION['_csrf'][$key] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf'][$key];
    }

    public static function field(string $key = 'default'): string
    {
        $token = self::token($key);

        return sprintf(
            '<input type="hidden" name="_csrf_key" value="%s"><input type="hidden" name="_token" value="%s">',
            e($key),
            e($token)
        );
    }

    public static function validate(string $token, string $key = 'default'): bool
    {
        $current = $_SESSION['_csrf'][$key] ?? null;
        if (!is_string($current) || $current === '') {
            return false;
        }

        return hash_equals($current, $token);
    }

    public static function wasProcessedRecently(string $token, int $seconds = 5): bool
    {
        self::cleanupProcessedTokens();
        $hash = hash('sha256', $token);
        $processedAt = $_SESSION['_csrf_processed'][$hash] ?? null;

        return is_int($processedAt) && (time() - $processedAt) < $seconds;
    }

    public static function markProcessed(string $token): void
    {
        $_SESSION['_csrf_processed'][hash('sha256', $token)] = time();
    }

    public static function refresh(string $key = 'default'): void
    {
        $_SESSION['_csrf'][$key] = bin2hex(random_bytes(32));
    }

    private static function cleanupProcessedTokens(): void
    {
        if (!isset($_SESSION['_csrf_processed']) || !is_array($_SESSION['_csrf_processed'])) {
            $_SESSION['_csrf_processed'] = [];
            return;
        }

        $now = time();
        foreach ($_SESSION['_csrf_processed'] as $hash => $processedAt) {
            if (!is_int($processedAt) || ($now - $processedAt) > 300) {
                unset($_SESSION['_csrf_processed'][$hash]);
            }
        }
    }
}

