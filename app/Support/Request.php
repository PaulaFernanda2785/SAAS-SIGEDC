<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $uri,
        private readonly array $queryParams,
        private readonly array $parsedBody,
        private readonly array $serverParams
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
        $uri = self::normalizeUri($uri);

        return new self(
            $method,
            $uri,
            $_GET,
            $_POST,
            $_SERVER
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->parsedBody);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->parsedBody)) {
            return $this->parsedBody[$key];
        }

        if (array_key_exists($key, $this->queryParams)) {
            return $this->queryParams[$key];
        }

        return $default;
    }

    public function ipAddress(): string
    {
        return (string) ($this->serverParams['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function userAgent(): string
    {
        return substr((string) ($this->serverParams['HTTP_USER_AGENT'] ?? 'unknown'), 0, 255);
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->serverParams[$key] ?? $default;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $normalized = strtoupper(str_replace('-', '_', trim($name)));
        if ($normalized === '') {
            return $default;
        }

        $serverKey = 'HTTP_' . $normalized;
        if (array_key_exists($serverKey, $this->serverParams)) {
            return $this->serverParams[$serverKey];
        }

        if ($normalized === 'CONTENT_TYPE' && array_key_exists('CONTENT_TYPE', $this->serverParams)) {
            return $this->serverParams['CONTENT_TYPE'];
        }

        if ($normalized === 'CONTENT_LENGTH' && array_key_exists('CONTENT_LENGTH', $this->serverParams)) {
            return $this->serverParams['CONTENT_LENGTH'];
        }

        if ($normalized === 'AUTHORIZATION') {
            if (array_key_exists('HTTP_AUTHORIZATION', $this->serverParams)) {
                return $this->serverParams['HTTP_AUTHORIZATION'];
            }
            if (array_key_exists('REDIRECT_HTTP_AUTHORIZATION', $this->serverParams)) {
                return $this->serverParams['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (array_key_exists('Authorization', $this->serverParams)) {
                return $this->serverParams['Authorization'];
            }
        }

        return $default;
    }

    private static function normalizeUri(string $uri): string
    {
        $basePath = \app_base_path();
        if (
            $basePath !== ''
            && ($uri === $basePath || str_starts_with($uri, $basePath . '/'))
        ) {
            $uri = substr($uri, strlen($basePath));
        }

        $normalized = '/' . ltrim($uri, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized === '' ? '/' : $normalized;
    }
}
