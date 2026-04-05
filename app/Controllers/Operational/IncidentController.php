<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Incident\IncidentService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class IncidentController
{
    public function __construct(private readonly ?IncidentService $incidentService = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $workspace = ($this->incidentService ?? new IncidentService())->workspaceData($auth);

        return Response::view('operational/incidents', [
            'title' => 'Gestao de Incidentes',
            'auth' => $auth,
            'scope' => $workspace['scope'],
            'incidents' => $workspace['incidents'],
            'incidentOptions' => $workspace['incident_options'],
            'periodOptions' => $workspace['period_options'],
            'unitOptions' => $workspace['unit_options'],
            'recentRecords' => $workspace['recent_records'],
            'statusOptions' => $workspace['status_options'],
            'commandStatusOptions' => $workspace['command_status_options'],
            'periodStatusOptions' => $workspace['period_status_options'],
            'recordTypeOptions' => $workspace['record_type_options'],
            'recordStatusOptions' => $workspace['record_status_options'],
            'criticalityOptions' => $workspace['criticality_options'],
            'classificationOptions' => $workspace['classification_options'],
        ], 'operational');
    }

    public function storeIncident(Request $request): Response
    {
        $result = ($this->incidentService ?? new IncidentService())
            ->openIncident($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/incidentes');
    }

    public function storeBriefing(Request $request): Response
    {
        $result = ($this->incidentService ?? new IncidentService())
            ->registerBriefing($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/incidentes');
    }

    public function upsertCommand(Request $request): Response
    {
        $result = ($this->incidentService ?? new IncidentService())
            ->upsertCommand($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/incidentes');
    }

    public function storePeriod(Request $request): Response
    {
        $result = ($this->incidentService ?? new IncidentService())
            ->createPeriod($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/incidentes');
    }

    public function storeRecord(Request $request): Response
    {
        $result = ($this->incidentService ?? new IncidentService())
            ->createRecord($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/incidentes');
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
