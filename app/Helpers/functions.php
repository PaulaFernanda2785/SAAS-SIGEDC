<?php

declare(strict_types=1);

use App\Support\Config;
use App\Support\Env;

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return BASE_PATH . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : '');
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        return base_path('resources' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '\\/') : ''));
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $configured = trim((string) config('app.base_path', ''));
        if ($configured === '' || $configured === '/') {
            return '';
        }

        return '/' . trim($configured, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string
    {
        if (preg_match('/^https?:\\/\\//i', $path) === 1) {
            return $path;
        }

        $basePath = app_base_path();
        $normalized = '/' . ltrim($path, '/');
        $normalized = $normalized === '//' ? '/' : $normalized;

        if ($normalized === '/') {
            return $basePath !== '' ? $basePath : '/';
        }

        return $basePath . $normalized;
    }
}

if (!function_exists('e')) {
    function e(string|null $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
