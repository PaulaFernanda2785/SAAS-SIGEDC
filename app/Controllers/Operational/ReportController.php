<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Reports\OperationalAdvancedReportService;
use App\Services\Reports\OperationalReportService;
use App\Support\Request;
use App\Support\Response;

final class ReportController
{
    public function __construct(
        private readonly ?OperationalReportService $reportService = null,
        private readonly ?OperationalAdvancedReportService $advancedReportService = null
    ) {
    }

    public function basic(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->reportService ?? new OperationalReportService())
            ->basicReport($auth, $request->all());

        return Response::view('operational/reports-basic', [
            'title' => 'Relatorio Operacional Basico',
            'auth' => $auth,
            'scope' => $data['scope'],
            'filters' => $data['filters'],
            'statusSummary' => $data['status_summary'],
            'recordsByType' => $data['records_by_type'],
            'incidents' => $data['incidents'],
            'recentRecords' => $data['recent_records'],
            'incidentOptions' => $data['incident_options'],
        ], 'operational');
    }

    public function advanced(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->advancedReportService ?? new OperationalAdvancedReportService())
            ->report($auth, $request->all());

        return Response::view('operational/reports-advanced', [
            'title' => 'Relatorio Operacional Avancado',
            'auth' => $auth,
            'scope' => $data['scope'],
            'filters' => $data['filters'],
            'trend' => $data['trend'],
            'hotspots' => $data['hotspots'],
            'auditFrequency' => $data['audit_frequency'],
            'documentsByEntity' => $data['documents_by_entity'],
            'activeAlerts' => $data['active_alerts'],
            'recentExecutions' => $data['recent_executions'],
        ], 'operational');
    }
}
