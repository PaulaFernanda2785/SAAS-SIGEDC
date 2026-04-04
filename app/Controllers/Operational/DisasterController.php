<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Incident\IncidentExpansionService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class DisasterController
{
    public function __construct(private readonly ?IncidentExpansionService $incidentExpansionService = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $workspace = ($this->incidentExpansionService ?? new IncidentExpansionService())->workspaceData($auth);

        return Response::view('operational/disaster-expansion', [
            'title' => 'Gerenciamento de Desastres',
            'auth' => $auth,
            'scope' => $workspace['scope'],
            'summary' => $workspace['summary'],
            'incidentOptions' => $workspace['incident_options'],
            'periodOptions' => $workspace['period_options'],
            'recentPai' => $workspace['recent_pai'],
            'recentOperations' => $workspace['recent_operations'],
            'recentPlanning' => $workspace['recent_planning'],
            'recentSafety' => $workspace['recent_safety'],
            'recentDemobilization' => $workspace['recent_demobilization'],
        ], 'operational');
    }

    public function storePai(Request $request): Response
    {
        $result = ($this->incidentExpansionService ?? new IncidentExpansionService())
            ->createPai($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/desastres');
    }

    public function storeOperation(Request $request): Response
    {
        $result = ($this->incidentExpansionService ?? new IncidentExpansionService())
            ->createOperation($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/desastres');
    }

    public function storePlanning(Request $request): Response
    {
        $result = ($this->incidentExpansionService ?? new IncidentExpansionService())
            ->createPlanning($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/desastres');
    }

    public function storeSafety(Request $request): Response
    {
        $result = ($this->incidentExpansionService ?? new IncidentExpansionService())
            ->createSafety($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/desastres');
    }

    public function storeDemobilization(Request $request): Response
    {
        $result = ($this->incidentExpansionService ?? new IncidentExpansionService())
            ->createDemobilization($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/desastres');
    }

    private function flashResult(array $result): void
    {
        if (($result['ok'] ?? false) === true) {
            Flash::set('success', (string) ($result['message'] ?? 'Operacao executada com sucesso.'));
            return;
        }

        Flash::set('error', (string) ($result['message'] ?? 'Falha ao processar operacao.'));
    }
}
