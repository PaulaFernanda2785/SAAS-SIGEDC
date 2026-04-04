<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Reports\OperationalReportService;
use App\Support\Request;
use App\Support\Response;

final class ReportController
{
    public function __construct(private readonly ?OperationalReportService $reportService = null)
    {
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
}
