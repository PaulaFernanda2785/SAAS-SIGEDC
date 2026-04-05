<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Plancon\PlanconService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class PlanconController
{
    public function __construct(private readonly ?PlanconService $planconService = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $workspace = ($this->planconService ?? new PlanconService())->workspaceData($auth);

        return Response::view('operational/plancon', [
            'title' => 'PLANCON e Gestao de Riscos',
            'auth' => $auth,
            'scope' => $workspace['scope'],
            'summary' => $workspace['summary'],
            'plancons' => $workspace['plancons'],
            'planconOptions' => $workspace['plancon_options'],
            'unitOptions' => $workspace['unit_options'],
            'recentRisks' => $workspace['recent_risks'],
            'recentScenarios' => $workspace['recent_scenarios'],
            'recentResources' => $workspace['recent_resources'],
        ], 'operational');
    }

    public function storePlancon(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createPlancon($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
    }

    public function storeRisk(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createRisk($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
    }

    public function storeScenario(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createScenario($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
    }

    public function storeActivationLevel(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createActivationLevel($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
    }

    public function storeResource(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createResource($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
    }

    public function storeReview(Request $request): Response
    {
        $result = ($this->planconService ?? new PlanconService())
            ->createReview($_SESSION['auth'] ?? [], $request->all(), $request);

        $this->flashResult($result);
        return Response::redirect('/operational/plancon');
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
