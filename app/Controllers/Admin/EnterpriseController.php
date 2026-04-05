<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\Enterprise\EnterpriseService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class EnterpriseController
{
    public function __construct(private readonly ?EnterpriseService $service = null)
    {
    }

    public function index(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $data = ($this->service ?? new EnterpriseService())->dashboardData($auth, $request->all());

        return Response::view('admin/enterprise', [
            'title' => 'Enterprise e Integracoes',
            'auth' => $auth,
            'summary' => $data['summary'],
            'featureFlags' => $data['feature_flags'],
            'apiApps' => $data['api_apps'],
            'integracoes' => $data['integracoes'],
            'automacoes' => $data['automacoes'],
            'slaPolicies' => $data['sla_politicas'],
            'tickets' => $data['tickets'],
            'digitalSignatures' => $data['assinaturas_digitais'],
            'executiveReports' => $data['relatorios_executivos'],
            'options' => $data['options'],
            'currentUfFilter' => $data['current_uf_filter'],
            'canSelectAllUf' => $data['can_select_all_uf'],
            'capabilities' => $data['capabilities'],
        ], 'admin');
    }

    public function storeFeature(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->registerFeature($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeApiApp(Request $request): Response
    {
        $result = ($this->service ?? new EnterpriseService())
            ->createApiClientApp($_SESSION['auth'] ?? [], $request->all(), $request);

        if (($result['ok'] ?? false) === true && isset($result['token_plain'])) {
            Flash::set(
                'success',
                (string) ($result['message'] ?? 'Cliente API criado.') . ' Token: ' . (string) $result['token_plain']
            );
        } elseif (($result['ok'] ?? false) === true) {
            Flash::set('success', (string) ($result['message'] ?? 'Operacao concluida.'));
        } else {
            Flash::set('error', (string) ($result['message'] ?? 'Falha na operacao enterprise.'));
        }

        return Response::redirect('/admin/enterprise');
    }

    public function storeIntegracao(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->createIntegracao($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeAutomacao(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->createAutomacao($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeSla(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->createSlaPolicy($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeTicket(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->createSupportTicket($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeDigitalSignature(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->registerDigitalSignature($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    public function storeExecutiveReport(Request $request): Response
    {
        return $this->handleAction(
            ($this->service ?? new EnterpriseService())->generateExecutiveReport($_SESSION['auth'] ?? [], $request->all(), $request),
            '/admin/enterprise'
        );
    }

    private function handleAction(array $result, string $redirect): Response
    {
        if (($result['ok'] ?? false) === true) {
            Flash::set('success', (string) ($result['message'] ?? 'Operacao concluida.'));
        } else {
            Flash::set('error', (string) ($result['message'] ?? 'Falha na operacao enterprise.'));
        }

        return Response::redirect($redirect);
    }
}
