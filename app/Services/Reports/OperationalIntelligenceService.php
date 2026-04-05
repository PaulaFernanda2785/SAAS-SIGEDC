<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Repositories\Reports\OperationalAlertRepository;
use App\Repositories\Reports\OperationalIntelligenceRepository;
use App\Services\Audit\AuditService;
use App\Services\Institutional\ScopeService;
use Throwable;

final class OperationalIntelligenceService
{
    public function __construct(
        private readonly ?OperationalIntelligenceRepository $repository = null,
        private readonly ?OperationalAlertRepository $alertRepository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?AuditService $auditService = null
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
                    'incidentes_sem_briefing' => 0,
                    'incidentes_com_briefing' => 0,
                    'media_minutos_primeiro_briefing' => null,
                    'menor_tempo_briefing' => null,
                    'maior_tempo_briefing' => null,
                ],
                'alerts' => [],
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

        $statusDistribution = $repository->incidentStatusDistribution($scope, $dateFrom, $dateTo);
        $hotspots = $repository->municipalityHotspots($scope, $dateFrom, $dateTo, 20);
        $mapPoints = $repository->mapPoints($scope, $dateFrom, $dateTo, $fallbackUf, 40);
        $responseKpi = $repository->responseTimeKpi($scope, $dateFrom, $dateTo);
        $planconCoverage = $repository->planconCoverage($scope);
        $trendByDay = $repository->trendByDay($scope, $dateFrom, $dateTo, 30);

        $alerts = [];
        try {
            $alertRepository = $this->alertRepository ?? new OperationalAlertRepository();
            $this->evaluateScopeAlerts($alertRepository, $scope, $hotspots, $responseKpi);
            $alerts = $alertRepository->activeAlerts($scope, 12);
        } catch (Throwable $exception) {
            ($this->auditService ?? new AuditService())->log([
                'conta_id' => $scope['conta_id'] ?? null,
                'orgao_id' => $scope['orgao_id'] ?? null,
                'unidade_id' => $scope['unidade_id'] ?? null,
                'usuario_id' => $auth['usuario_id'] ?? null,
                'modulo_codigo' => 'INTELLIGENCE',
                'acao' => 'INTELLIGENCE_ALERT_ENGINE_FAILURE',
                'resultado' => 'FALHA',
                'entidade_tipo' => 'inteligencia_alertas_operacionais',
                'entidade_id' => null,
                'detalhes' => [
                    'error' => $exception->getMessage(),
                ],
            ]);
        }

        return [
            'scope' => $scope,
            'filters' => $normalizedFilters,
            'status_distribution' => $statusDistribution,
            'hotspots' => $hotspots,
            'map_points' => $mapPoints,
            'response_kpi' => $responseKpi,
            'alerts' => $alerts,
            'plancon_coverage' => $planconCoverage,
            'trend_by_day' => $trendByDay,
        ];
    }

    private function evaluateScopeAlerts(
        OperationalAlertRepository $alertRepository,
        array $scope,
        array $hotspots,
        array $responseKpi
    ): void {
        $hotspotThreshold = max(1, (int) config('intelligence.hotspot_incidents_threshold', 5));
        $briefingThreshold = max(1, (int) config('intelligence.briefing_delay_minutes_threshold', 120));

        $peakHotspot = $hotspots[0] ?? null;
        $peakHotspotTotal = (int) ($peakHotspot['total_incidentes'] ?? 0);
        if ($peakHotspotTotal >= $hotspotThreshold) {
            $alertRepository->upsertScopeAlert(
                $scope,
                'HOTSPOT_CONCENTRACAO_ALTA',
                'ALTO',
                sprintf(
                    'Hotspot critico em %s com %d incidentes no periodo monitorado.',
                    (string) ($peakHotspot['municipio'] ?? 'municipio nao identificado'),
                    $peakHotspotTotal
                ),
                null,
                'REGRAS_INTELIGENCIA',
                [
                    'municipio' => (string) ($peakHotspot['municipio'] ?? ''),
                    'total_incidentes' => $peakHotspotTotal,
                ]
            );
        } else {
            $alertRepository->closeScopeAlert($scope, 'HOTSPOT_CONCENTRACAO_ALTA');
        }

        $totalIncidents = (int) ($responseKpi['total_incidentes'] ?? 0);
        $incidentsWithoutBriefing = (int) ($responseKpi['incidentes_sem_briefing'] ?? 0);
        $averageBriefing = $responseKpi['media_minutos_primeiro_briefing'] ?? null;
        if ($totalIncidents > 0 && $incidentsWithoutBriefing > 0) {
            $alertRepository->upsertScopeAlert(
                $scope,
                'BRIEFING_INICIAL_AUSENTE',
                'CRITICO',
                sprintf(
                    'Ha %d incidente(s) sem briefing inicial registrado no periodo analisado.',
                    $incidentsWithoutBriefing
                ),
                null,
                'REGRAS_INTELIGENCIA',
                [
                    'total_incidentes' => $totalIncidents,
                    'incidentes_sem_briefing' => $incidentsWithoutBriefing,
                ]
            );
        } else {
            $alertRepository->closeScopeAlert($scope, 'BRIEFING_INICIAL_AUSENTE');
        }

        $averageBriefingMinutes = is_numeric($averageBriefing) ? (float) $averageBriefing : null;
        if ($averageBriefingMinutes !== null && $averageBriefingMinutes > $briefingThreshold) {
            $alertRepository->upsertScopeAlert(
                $scope,
                'TEMPO_BRIEFING_ACIMA_LIMITE',
                'MODERADO',
                sprintf(
                    'Tempo medio do primeiro briefing acima do limite: %.1f min.',
                    $averageBriefingMinutes
                ),
                null,
                'REGRAS_INTELIGENCIA',
                [
                    'media_minutos_primeiro_briefing' => $averageBriefingMinutes,
                    'limite_minutos' => $briefingThreshold,
                ]
            );
        } else {
            $alertRepository->closeScopeAlert($scope, 'TEMPO_BRIEFING_ACIMA_LIMITE');
        }
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
