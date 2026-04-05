<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Repositories\Audit\GovernanceRepository;
use App\Repositories\Operational\DocumentRepository;
use App\Repositories\Reports\AdvancedReportRepository;
use App\Repositories\Reports\OperationalAlertRepository;
use App\Repositories\Reports\OperationalIntelligenceRepository;
use App\Services\Institutional\ScopeService;

final class OperationalAdvancedReportService
{
    public function __construct(
        private readonly ?OperationalIntelligenceRepository $intelligenceRepository = null,
        private readonly ?GovernanceRepository $governanceRepository = null,
        private readonly ?DocumentRepository $documentRepository = null,
        private readonly ?OperationalAlertRepository $alertRepository = null,
        private readonly ?AdvancedReportRepository $advancedReportRepository = null,
        private readonly ?ScopeService $scopeService = null
    ) {
    }

    public function report(
        array $auth,
        array $filters,
        bool $registerExecution = true,
        ?string $executionFilePath = null,
        string $executionType = 'OPERACIONAL_AVANCADO'
    ): array
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
                'trend' => [],
                'hotspots' => [],
                'audit_frequency' => [],
                'documents_by_entity' => [],
                'active_alerts' => [],
                'recent_executions' => [],
                'total_records' => 0,
            ];
        }

        $intelligenceRepository = $this->intelligenceRepository ?? new OperationalIntelligenceRepository();
        $governanceRepository = $this->governanceRepository ?? new GovernanceRepository();
        $documentRepository = $this->documentRepository ?? new DocumentRepository();
        $alertRepository = $this->alertRepository ?? new OperationalAlertRepository();
        $advancedReportRepository = $this->advancedReportRepository ?? new AdvancedReportRepository();

        $trend = $intelligenceRepository->trendByDay($scope, $dateFrom, $dateTo, 45);
        $hotspots = $intelligenceRepository->municipalityHotspots($scope, $dateFrom, $dateTo, 30);
        $auditFrequency = $governanceRepository->actionFrequency($scope, $dateFrom, $dateTo, 30);
        $documentsByEntity = $documentRepository->attachmentsByEntityType($scope);
        $activeAlerts = $alertRepository->activeAlerts($scope, 25);

        $totalRecords = count($trend) + count($hotspots) + count($auditFrequency) + count($documentsByEntity) + count($activeAlerts);
        if ($registerExecution) {
            $advancedReportRepository->registerExecution([
                'conta_id' => $auth['conta_id'] ?? null,
                'orgao_id' => $auth['orgao_id'] ?? null,
                'unidade_id' => $auth['unidade_id'] ?? null,
                'usuario_id' => $auth['usuario_id'] ?? null,
                'tipo_relatorio' => $executionType,
                'filtros' => $normalizedFilters,
                'status_execucao' => 'CONCLUIDO',
                'total_registros' => $totalRecords,
                'arquivo_caminho' => $executionFilePath,
            ]);
        }

        return [
            'scope' => $scope,
            'filters' => $normalizedFilters,
            'trend' => $trend,
            'hotspots' => $hotspots,
            'audit_frequency' => $auditFrequency,
            'documents_by_entity' => $documentsByEntity,
            'active_alerts' => $activeAlerts,
            'recent_executions' => $advancedReportRepository->recentExecutions($scope, 30),
            'total_records' => $totalRecords,
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
