<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Services\SaaS\PublicOnboardingService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class OnboardingController
{
    public function __construct(private readonly ?PublicOnboardingService $publicOnboardingService = null)
    {
    }

    public function showDemo(Request $request): Response
    {
        $selectedPlan = strtoupper(trim((string) $request->input('plano', '')));
        $selectedCycle = strtoupper(trim((string) $request->input('ciclo', '')));

        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $selection = $service->resolveSelection($selectedPlan, $selectedCycle);
        if ($selection === null) {
            return Response::redirect('/planos');
        }

        return Response::view('public/demo', [
            'title' => 'Demonstracao e Assinatura do SIGERD',
            'selectedPlan' => $selectedPlan,
            'selectedCycle' => $selectedCycle,
            'selection' => $selection,
            'ufs' => $service->publicUfs(),
            'errors' => Flash::get('errors', []),
        ], 'public');
    }

    public function startTrial(Request $request): Response
    {
        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $result = $service->startTrial($request->all(), $request);

        $redirect = $this->demoRedirectPath($request);
        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Nao foi possivel iniciar a demonstracao.'));
            $errors = $result['errors'] ?? [];
            if (is_array($errors) && $errors !== []) {
                Flash::set('errors', $errors);
            }
            Flash::setOldInput($this->oldInputFromRequest($request));

            return Response::redirect($redirect);
        }

        $expiration = (string) ($result['trial_expira_em'] ?? '');
        Flash::set('success', 'Demonstracao ativada por 3 dias. Expira em: ' . ($expiration !== '' ? $expiration : 'N/D') . '.');
        Flash::set('warning', 'Apos o periodo de demonstracao, sera necessario escolher um plano e concluir o pagamento para continuar o acesso.');

        return Response::redirect((string) ($result['redirect'] ?? '/acessar-plataforma'));
    }

    public function startSubscription(Request $request): Response
    {
        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $result = $service->startDirectSubscription($request->all(), $request);

        $redirect = $this->demoRedirectPath($request);
        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Nao foi possivel iniciar a assinatura.'));
            $errors = $result['errors'] ?? [];
            if (is_array($errors) && $errors !== []) {
                Flash::set('errors', $errors);
            }
            Flash::setOldInput($this->oldInputFromRequest($request));

            return Response::redirect($redirect);
        }

        Flash::set('success', (string) ($result['message'] ?? 'Cadastro concluido.'));

        $checkoutToken = trim((string) ($result['checkout_token'] ?? ''));
        if ($checkoutToken === '') {
            Flash::set('error', 'Checkout indisponivel. Tente novamente.');
            return Response::redirect($redirect);
        }

        return Response::redirect('/checkout?token=' . rawurlencode($checkoutToken));
    }

    public function checkout(Request $request): Response
    {
        $token = trim((string) $request->input('token', ''));
        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $summary = $service->checkoutSummary($token);

        if (!($summary['ok'] ?? false)) {
            Flash::set('error', (string) ($summary['message'] ?? 'Checkout nao localizado.'));
            return Response::redirect('/planos');
        }

        return Response::view('public/checkout', [
            'title' => 'Checkout da assinatura',
            'checkout' => $summary['data'] ?? [],
        ], 'public');
    }

    public function confirmCheckout(Request $request): Response
    {
        $token = trim((string) $request->input('checkout_token', ''));
        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $result = $service->confirmCheckout($token, $request, 'MANUAL');

        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao confirmar pagamento.'));
            return Response::redirect('/checkout?token=' . rawurlencode($token));
        }

        Flash::set('success', (string) ($result['message'] ?? 'Pagamento aprovado com sucesso.'));
        $email = trim((string) ($result['email_login'] ?? ''));
        if ($email !== '') {
            Flash::set('warning', 'Login para acesso: ' . $email);
        }

        return Response::redirect('/acessar-plataforma');
    }

    public function mercadoPagoWebhook(Request $request): Response
    {
        $service = $this->publicOnboardingService ?? new PublicOnboardingService();
        $result = $service->processWebhook($request->all(), $request);
        $status = isset($result['status']) ? (int) $result['status'] : 200;

        return Response::json([
            'ok' => (bool) ($result['ok'] ?? false),
            'message' => (string) ($result['message'] ?? null),
        ], $status);
    }

    private function demoRedirectPath(Request $request): string
    {
        $plan = strtoupper(trim((string) $request->input('plan_code', $request->input('plano', ''))));
        $cycle = strtoupper(trim((string) $request->input('billing_cycle', $request->input('ciclo', ''))));

        if ($plan === '' || $cycle === '') {
            return '/planos';
        }

        return '/demonstracao?plano=' . rawurlencode($plan) . '&ciclo=' . rawurlencode($cycle);
    }

    private function oldInputFromRequest(Request $request): array
    {
        $input = $request->all();
        unset($input['password'], $input['password_confirmation'], $input['_token'], $input['_csrf_key']);

        return $input;
    }
}
