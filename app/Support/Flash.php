<?php

declare(strict_types=1);

namespace App\Support;

final class Flash
{
    public static function set(string $key, mixed $value): void
    {
        $_SESSION['_flash']['next'][$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash']['current'][$key] ?? $default;
    }

    public static function all(): array
    {
        return $_SESSION['_flash']['current'] ?? [];
    }

    public static function setOldInput(array $input): void
    {
        $_SESSION['_flash']['old_next'] = $input;
    }

    public static function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_flash']['old_current'][$key] ?? $default;
    }
}

