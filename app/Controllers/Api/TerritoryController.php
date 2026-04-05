<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Domain\Enum\BrazilUf;
use App\Services\Territory\TerritoryLookupService;
use App\Support\Request;
use App\Support\Response;

final class TerritoryController
{
    public function __construct(private readonly ?TerritoryLookupService $territoryLookupService = null)
    {
    }

    public function ufs(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $items = ($this->territoryLookupService ?? new TerritoryLookupService())->accessibleUfs($auth);

        return Response::json([
            'ok' => true,
            'message' => null,
            'data' => $items,
        ]);
    }

    public function municipios(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $ufRequested = BrazilUf::normalize($request->input('uf'));
        $query = trim((string) $request->input('q', ''));
        $territoryLookupService = $this->territoryLookupService ?? new TerritoryLookupService();

        if ($ufRequested === null) {
            return Response::json([
                'ok' => false,
                'message' => 'Parametro uf e obrigatorio.',
                'data' => [],
            ], 422);
        }

        if (!$territoryLookupService->canReadUf($auth, $ufRequested)) {
            return Response::json([
                'ok' => false,
                'message' => 'Acesso negado para consultar municipios deste UF.',
                'data' => [],
            ], 403);
        }

        $limit = (int) $request->input('limit', 25);
        if ($limit < 1) {
            $limit = 25;
        }
        if ($limit > 50) {
            $limit = 50;
        }

        $items = $territoryLookupService->municipiosByUf($auth, $ufRequested, $query, $limit);

        return Response::json([
            'ok' => true,
            'message' => null,
            'data' => $items,
        ]);
    }
}
