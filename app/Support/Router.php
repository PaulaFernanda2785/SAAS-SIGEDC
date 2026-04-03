<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly array $middlewareAliases = [])
    {
    }

    public function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    public function add(string $method, string $path, callable|array $handler, array $middlewares = []): void
    {
        $normalizedPath = $this->normalizePath($path);
        $this->routes[strtoupper($method)][$normalizedPath] = [
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $methodRoutes = $this->routes[$request->method()] ?? [];
        $route = $methodRoutes[$this->normalizePath($request->uri())] ?? null;

        if ($route === null) {
            return Response::view('errors/404', ['title' => 'Pagina nao encontrada'], 'public', 404);
        }

        $handler = $route['handler'];
        $middlewares = $this->resolveMiddlewares($route['middlewares']);

        $next = function (Request $request) use ($handler): Response {
            return $this->runHandler($handler, $request);
        };

        foreach (array_reverse($middlewares) as $middlewareClass) {
            $next = function (Request $request) use ($middlewareClass, $next): Response {
                $middleware = new $middlewareClass();
                return $middleware->handle($request, $next);
            };
        }

        return $next($request);
    }

    private function runHandler(callable|array $handler, Request $request): Response
    {
        $response = null;

        if (is_callable($handler)) {
            $response = $handler($request);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $response = $controller->{$method}($request);
        }

        if ($response instanceof Response) {
            return $response;
        }

        if (is_string($response)) {
            return Response::html($response);
        }

        return Response::html('');
    }

    private function resolveMiddlewares(array $middlewares): array
    {
        $resolved = [];
        foreach ($middlewares as $middleware) {
            if (class_exists($middleware)) {
                $resolved[] = $middleware;
                continue;
            }

            if (isset($this->middlewareAliases[$middleware])) {
                $resolved[] = $this->middlewareAliases[$middleware];
                continue;
            }

            throw new RuntimeException("Middleware nao encontrado: {$middleware}");
        }

        return $resolved;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . ltrim($path, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }

        return $normalized === '' ? '/' : $normalized;
    }
}

