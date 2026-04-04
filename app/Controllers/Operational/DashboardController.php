<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Incident\IncidentService;
use App\Support\Request;
use App\Support\Response;

final class DashboardController
{
    public function __construct(private readonly ?IncidentService $incidentService = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $dashboard = ($this->incidentService ?? new IncidentService())->dashboardData($auth);

        return Response::view('operational/dashboard', [
            'title' => 'Painel Operacional Institucional',
            'auth' => $auth,
            'summary' => $dashboard['summary'],
            'recentIncidents' => $dashboard['recent_incidents'],
            'scope' => $dashboard['scope'],
        ], 'operational');
    }
}
