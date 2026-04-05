<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Services\Audit\GovernanceService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class GovernanceController
{
    public function __construct(private readonly ?GovernanceService $service = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->service ?? new GovernanceService())->workspaceData($auth, $request->all());

        return Response::view('operational/governance', [
            'title' => 'Governanca Operacional',
            'auth' => $auth,
            'scope' => $data['scope'],
            'filters' => $data['filters'],
            'summary' => $data['summary'],
            'actionFrequency' => $data['action_frequency'],
            'recentLogs' => $data['recent_logs'],
            'recentTermAcceptances' => $data['recent_term_acceptances'],
            'term' => $data['term'],
            'termAccepted' => $data['term_accepted'],
        ], 'operational');
    }

    public function acceptTerm(Request $request): Response
    {
        $result = ($this->service ?? new GovernanceService())->acceptCurrentTerm($_SESSION['auth'] ?? [], $request);

        if (($result['ok'] ?? false) === true) {
            Flash::set('success', (string) ($result['message'] ?? 'Aceite de termo registrado com sucesso.'));
        } else {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao registrar aceite de termo.'));
        }

        return Response::redirect('/operational/governanca');
    }
}
