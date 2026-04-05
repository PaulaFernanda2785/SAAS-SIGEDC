<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\Enterprise\EnterpriseService;
use App\Support\Request;
use App\Support\Response;

final class EnterpriseApiController
{
    public function __construct(private readonly ?EnterpriseService $service = null)
    {
    }

    public function executiveSummary(Request $request): Response
    {
        $apiAuth = $_SESSION['api_auth'] ?? null;
        if (!is_array($apiAuth)) {
            return Response::json([
                'ok' => false,
                'message' => 'Contexto API nao autenticado.',
                'data' => [],
            ], 401);
        }

        $persist = (string) $request->input('persistir', '0') === '1';
        $result = ($this->service ?? new EnterpriseService())
            ->apiExecutiveSummary($apiAuth, $request->all(), $persist);

        return Response::json([
            'ok' => (bool) ($result['ok'] ?? false),
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? [],
        ], (int) ($result['status'] ?? 200));
    }
}
