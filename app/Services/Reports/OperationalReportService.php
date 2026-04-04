<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Repositories\Operational\IncidentRepository;
use App\Services\Institutional\ScopeService;

final class OperationalReportService
{
    public function __construct(
        private readonly ?IncidentRepository $incidentRepository = null,
        private readonly ?ScopeService $scopeService = null
    ) {
    }

    public function basicReport(array $auth, array $filters): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);

        $status = $this->sanitizeEnum(
            (string) ($filters['status_incidente'] ?? ''),
            ['ABERTO', 'EM_ANDAMENTO', 'CONTROLADO', 'ENCERRADO'],
            ''
        );
        $status = $status === '' ? null : $status;
        $dateFrom = $this->sanitizeDate($filters['data_inicio'] ?? null);
        $dateTo = $this->sanitizeDate($filters['data_fim'] ?? null);
        $incidentId = $this->nullableInt($filters['incidente_id'] ?? null);

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'filters' => [
                    'status_incidente' => $status,
                    'data_inicio' => $dateFrom,
                    'data_fim' => $dateTo,
                    'incidente_id' => $incidentId,
                ],
                'status_summary' => [],
                'records_by_type' => [],
                'incidents' => [],
                'recent_records' => [],
                'incident_options' => [],
            ];
        }

        $repository = $this->incidentRepository ?? new IncidentRepository();

        return [
            'scope' => $scope,
            'filters' => [
                'status_incidente' => $status,
                'data_inicio' => $dateFrom,
                'data_fim' => $dateTo,
                'incidente_id' => $incidentId,
            ],
            'status_summary' => $repository->reportStatusSummary($scope, $dateFrom, $dateTo),
            'records_by_type' => $repository->reportRecordsByType($scope, $dateFrom, $dateTo, $incidentId),
            'incidents' => $repository->reportIncidents($scope, $status, $dateFrom, $dateTo, 200),
            'recent_records' => $repository->reportRecentRecords($scope, $dateFrom, $dateTo, $incidentId, 160),
            'incident_options' => $repository->incidentOptions($scope),
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

    private function nullableInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
