<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Reports\OperationalIntelligenceService;
use App\Support\Request;
use App\Support\Response;

final class IntelligenceController
{
    public function __construct(private readonly ?OperationalIntelligenceService $service = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->service ?? new OperationalIntelligenceService())->dashboard($auth, $request->all());

        return Response::view('operational/intelligence', [
            'title' => 'Inteligencia Operacional',
            'auth' => $auth,
            'scope' => $data['scope'],
            'filters' => $data['filters'],
            'statusDistribution' => $data['status_distribution'],
            'hotspots' => $data['hotspots'],
            'mapPoints' => $data['map_points'],
            'responseKpi' => $data['response_kpi'],
            'planconCoverage' => $data['plancon_coverage'],
            'trendByDay' => $data['trend_by_day'],
        ], 'operational');
    }
}
