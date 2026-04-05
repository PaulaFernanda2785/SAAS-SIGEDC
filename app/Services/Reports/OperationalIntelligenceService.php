<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Repositories\Reports\OperationalIntelligenceRepository;
use App\Services\Institutional\ScopeService;

final class OperationalIntelligenceService
{
    public function __construct(
        private readonly ?OperationalIntelligenceRepository $repository = null,
        private readonly ?ScopeService $scopeService = null
    ) {
    }

    public function dashboard(array $auth, array $filters): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);
        $dateFrom = $this->sanitizeDate($filters['data_inicio'] ?? null);
        $dateTo = $this->sanitizeDate($filters['data_fim'] ?? null);

        $normalizedFilters = [
            'data_inicio' => $dateFrom,
            'data_fim' => $dateTo,
        ];

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'filters' => $normalizedFilters,
                'status_distribution' => [],
                'hotspots' => [],
                'map_points' => [],
                'response_kpi' => [
                    'total_incidentes' => 0,
                    'media_minutos_primeiro_briefing' => null,
                    'menor_tempo_briefing' => null,
                    'maior_tempo_briefing' => null,
                ],
                'plancon_coverage' => [
                    'total_plancons' => 0,
                    'total_plancons_ativos' => 0,
                    'total_plancons_vencidos' => 0,
                ],
                'trend_by_day' => [],
            ];
        }

        $repository = $this->repository ?? new OperationalIntelligenceRepository();
        $fallbackUf = strtoupper(trim((string) ($auth['uf_sigla'] ?? '')));
        if (strlen($fallbackUf) !== 2) {
            $fallbackUf = 'TO';
        }

        return [
            'scope' => $scope,
            'filters' => $normalizedFilters,
            'status_distribution' => $repository->incidentStatusDistribution($scope, $dateFrom, $dateTo),
            'hotspots' => $repository->municipalityHotspots($scope, $dateFrom, $dateTo, 20),
            'map_points' => $repository->mapPoints($scope, $dateFrom, $dateTo, $fallbackUf, 40),
            'response_kpi' => $repository->responseTimeKpi($scope, $dateFrom, $dateTo),
            'plancon_coverage' => $repository->planconCoverage($scope),
            'trend_by_day' => $repository->trendByDay($scope, $dateFrom, $dateTo, 30),
        ];
    }

    private function sanitizeDate(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1 ? $raw : null;
    }
}
