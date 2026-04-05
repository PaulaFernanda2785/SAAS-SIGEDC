<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Enterprise\EnterpriseService;
use App\Support\Request;
use App\Support\Response;

final class AuthenticateApiKey implements MiddlewareInterface
{
    public function __construct(private readonly ?EnterpriseService $enterpriseService = null)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        unset($_SESSION['api_auth']);

        $headerName = (string) config('enterprise.api_header_name', 'X-Api-Key');
        $token = trim((string) $request->header($headerName, ''));

        if ($token === '') {
            $authorization = trim((string) $request->header('Authorization', ''));
            if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) === 1) {
                $token = trim((string) ($matches[1] ?? ''));
            }
        }

        $result = ($this->enterpriseService ?? new EnterpriseService())
            ->authenticateApiToken($token, $request);

        if (($result['ok'] ?? false) !== true) {
            return Response::json([
                'ok' => false,
                'message' => (string) ($result['message'] ?? 'Token de API invalido.'),
                'data' => [],
            ], (int) ($result['status'] ?? 401));
        }

        $_SESSION['api_auth'] = $result['api_auth'] ?? null;

        return $next($request);
    }
}
