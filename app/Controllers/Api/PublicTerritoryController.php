<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Services\Territory\TerritoryLookupService;
use App\Support\Request;
use App\Support\Response;

final class PublicTerritoryController
{
    public function __construct(private readonly ?TerritoryLookupService $territoryLookupService = null)
    {
    }

    public function ufs(Request $request): Response
    {
        $lookup = $this->territoryLookupService ?? new TerritoryLookupService();
        $items = $lookup->accessibleUfs($this->publicAuthContext());

        return Response::json([
            'ok' => true,
            'message' => null,
            'data' => $items,
        ]);
    }

    public function municipios(Request $request): Response
    {
        $ufRequested = BrazilUf::normalize($request->input('uf'));
        $query = trim((string) $request->input('q', ''));

        if ($ufRequested === null) {
            return Response::json([
                'ok' => false,
                'message' => 'Parametro uf e obrigatorio.',
                'data' => [],
            ], 422);
        }

        $limit = (int) $request->input('limit', 20);
        if ($limit < 1) {
            $limit = 20;
        }
        if ($limit > 30) {
            $limit = 30;
        }

        $lookup = $this->territoryLookupService ?? new TerritoryLookupService();
        $items = $lookup->municipiosByUf($this->publicAuthContext(), $ufRequested, $query, $limit);

        return Response::json([
            'ok' => true,
            'message' => null,
            'data' => $items,
        ]);
    }

    private function publicAuthContext(): array
    {
        return [
            'uf_sigla' => null,
            'perfis' => [UserProfile::ADMIN_MASTER],
        ];
    }
}
