<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Repositories\Auth\UserRepository;
use App\Repositories\SaaS\BillingRepository;
use App\Repositories\SaaS\CommercialRepository;
use App\Repositories\SaaS\InstitutionRepository;
use App\Repositories\Territory\TerritoryRepository;
use App\Services\Audit\AuditService;
use App\Services\Payments\MercadoPagoGatewayService;
use App\Support\Logger;
use App\Support\Request;
use DateTimeImmutable;
use Throwable;

final class PublicOnboardingService
{
    private const TRIAL_DAYS = 3;
    private const DEMO_REASON = 'TRIAL_DEMO_PUBLICO_3_DIAS';
    private const PAYMENT_PENDING_REASON = 'AGUARDANDO_PAGAMENTO_PORTAL_PUBLICO';

    public function __construct(
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?CommercialRepository $commercialRepository = null,
        private readonly ?BillingRepository $billingRepository = null,
        private readonly ?UserRepository $userRepository = null,
        private readonly ?TerritoryRepository $territoryRepository = null,
        private readonly ?PublicPlanCatalogService $planCatalogService = null,
        private readonly ?MercadoPagoGatewayService $mercadoPagoGatewayService = null,
        private readonly ?AuditService $auditService = null,
    ) {
    }

    public function publicUfs(): array
    {
        return ($this->territoryRepository ?? new TerritoryRepository())->ufs();
    }

    public function resolveSelection(string $planCode, string $billingCycle): ?array
    {
        return ($this->planCatalogService ?? new PublicPlanCatalogService(
            $this->commercialRepository ?? new CommercialRepository()
        ))->findSelectedOption($planCode, $billingCycle);
    }

    public function startTrial(array $input, Request $request): array
    {
        $data = $this->sanitizeInput($input);
        $selection = $this->resolveSelection($data['plan_code'], $data['billing_cycle']);
        if ($selection === null) {
            return ['ok' => false, 'message' => 'Plano/ciclo invalido para demonstracao.'];
        }

        $validationErrors = $this->validateInput($data);
        if ($validationErrors !== []) {
            return ['ok' => false, 'message' => $validationErrors[0], 'errors' => $validationErrors];
        }

        $existingUser = ($this->userRepository ?? new UserRepository())->findByLogin($data['email_login']);
        if ($existingUser !== null) {
            return [
                'ok' => false,
                'message' => 'Ja existe cadastro para este email. Use a assinatura direta com o mesmo login/senha para continuar.',
            ];
        }

        $pdo = ($this->billingRepository ?? new BillingRepository())->pdo();

        $ownsTransaction = !$pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            $contaId = ($this->institutionRepository ?? new InstitutionRepository())->createAccount([
                'nome_fantasia' => $data['nome_conta'],
                'razao_social' => $data['nome_conta'],
                'cpf_cnpj' => null,
                'uf_sigla' => $data['uf_sigla'],
                'email_principal' => $data['email_login'],
                'status_cadastral' => 'ATIVA',
            ]);

            $orgaoId = ($this->institutionRepository ?? new InstitutionRepository())->createOrgao([
                'conta_id' => $contaId,
                'nome_oficial' => $data['nome_orgao'],
                'sigla' => $this->buildOrgaoSigla($data['nome_orgao']),
                'cnpj' => null,
                'uf_sigla' => $data['uf_sigla'],
                'status_orgao' => 'ATIVO',
            ]);

            $unidadeId = ($this->institutionRepository ?? new InstitutionRepository())->createUnidade([
                'orgao_id' => $orgaoId,
                'codigo_unidade' => 'SEDE',
                'nome_unidade' => $data['nome_unidade'],
                'tipo_unidade' => 'SEDE',
                'uf_sigla' => $data['uf_sigla'],
                'status_unidade' => 'ATIVA',
            ]);

            $usuarioId = ($this->institutionRepository ?? new InstitutionRepository())->createUsuario([
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $unidadeId,
                'uf_sigla' => $data['uf_sigla'],
                'nome_completo' => $data['nome_responsavel'],
                'email_login' => $data['email_login'],
                'matricula_funcional' => null,
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'status_usuario' => 'ATIVO',
            ]);

            $this->attachProfile($usuarioId, UserProfile::LEITOR);

            $startDate = (new DateTimeImmutable('today'))->format('Y-m-d');
            $expireDate = (new DateTimeImmutable('today'))
                ->modify('+' . self::TRIAL_DAYS . ' days')
                ->format('Y-m-d');

            $assinaturaId = ($this->commercialRepository ?? new CommercialRepository())->createAssinatura([
                'conta_id' => $contaId,
                'uf_sigla' => $data['uf_sigla'],
                'plano_id' => (int) $selection['id'],
                'status_assinatura' => 'TRIAL',
                'inicia_em' => $startDate,
                'expira_em' => $expireDate,
                'trial_fim_em' => $expireDate,
                'motivo_status' => self::DEMO_REASON,
            ]);

            $this->activateModulesForPlan($assinaturaId, (string) ($selection['codigo_plano'] ?? ''));

            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            $this->audit('PUBLIC', 'PUBLIC_TRIAL_STARTED', 'assinaturas', $assinaturaId, [
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $unidadeId,
                'usuario_id' => $usuarioId,
                'uf_sigla' => $data['uf_sigla'],
                'municipio_nome' => $data['municipio_nome'],
                'plan_code' => $selection['codigo_plano'] ?? null,
                'billing_cycle' => $data['billing_cycle'],
                'trial_expira_em' => $expireDate,
            ], $request);

            return [
                'ok' => true,
                'message' => 'Demonstracao iniciada por 3 dias. Acesso liberado em modo demonstrativo sem geracao de produtos.',
                'redirect' => '/acessar-plataforma',
                'trial_expira_em' => $expireDate,
                'email_login' => $data['email_login'],
            ];
        } catch (Throwable $exception) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Logger::error('public_onboarding', 'Falha ao iniciar demonstracao publica.', [
                'error' => $exception->getMessage(),
                'email_login' => $data['email_login'],
                'uf_sigla' => $data['uf_sigla'],
            ]);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel iniciar a demonstracao agora. Tente novamente em instantes.',
            ];
        }
    }

    public function startDirectSubscription(array $input, Request $request): array
    {
        if (!(($this->billingRepository ?? new BillingRepository())->billingSchemaReady())) {
            return [
                'ok' => false,
                'message' => 'Estrutura de faturamento nao encontrada. Aplique o schema 008 da fase de onboarding/pagamentos.',
            ];
        }

        $data = $this->sanitizeInput($input);
        $selection = $this->resolveSelection($data['plan_code'], $data['billing_cycle']);
        if ($selection === null) {
            return ['ok' => false, 'message' => 'Plano/ciclo invalido para assinatura.'];
        }

        $validationErrors = $this->validateInput($data);
        if ($validationErrors !== []) {
            return ['ok' => false, 'message' => $validationErrors[0], 'errors' => $validationErrors];
        }

        $userRepository = $this->userRepository ?? new UserRepository();
        $existingUser = $userRepository->findByLogin($data['email_login']);

        $contaId = 0;
        $orgaoId = 0;
        $unidadeId = null;
        $usuarioId = 0;

        if ($existingUser !== null) {
            if (!password_verify($data['password'], (string) ($existingUser['password_hash'] ?? ''))) {
                return [
                    'ok' => false,
                    'message' => 'Login ja existente. Para migrar da demonstracao para assinatura, informe a senha atual desse login.',
                ];
            }

            $existingUf = BrazilUf::normalize($existingUser['uf_sigla'] ?? null);
            if ($existingUf === null || $existingUf !== $data['uf_sigla']) {
                return [
                    'ok' => false,
                    'message' => 'UF informado diverge do cadastro ja existente para este login.',
                ];
            }

            $contaId = (int) ($existingUser['conta_id'] ?? 0);
            $orgaoId = (int) ($existingUser['orgao_id'] ?? 0);
            $unidadeId = isset($existingUser['unidade_id']) ? (int) $existingUser['unidade_id'] : null;
            $usuarioId = (int) ($existingUser['id'] ?? 0);

            $latestAssinatura = ($this->commercialRepository ?? new CommercialRepository())->latestAssinaturaByConta($contaId);
            if ($this->hasValidContract($latestAssinatura)) {
                return [
                    'ok' => false,
                    'message' => 'Essa conta ja possui assinatura valida. Utilize o acesso da plataforma.',
                ];
            }
        }

        $pdo = ($this->billingRepository ?? new BillingRepository())->pdo();
        $ownsTransaction = !$pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            if ($existingUser === null) {
                $contaId = ($this->institutionRepository ?? new InstitutionRepository())->createAccount([
                    'nome_fantasia' => $data['nome_conta'],
                    'razao_social' => $data['nome_conta'],
                    'cpf_cnpj' => null,
                    'uf_sigla' => $data['uf_sigla'],
                    'email_principal' => $data['email_login'],
                    'status_cadastral' => 'ATIVA',
                ]);

                $orgaoId = ($this->institutionRepository ?? new InstitutionRepository())->createOrgao([
                    'conta_id' => $contaId,
                    'nome_oficial' => $data['nome_orgao'],
                    'sigla' => $this->buildOrgaoSigla($data['nome_orgao']),
                    'cnpj' => null,
                    'uf_sigla' => $data['uf_sigla'],
                    'status_orgao' => 'ATIVO',
                ]);

                $unidadeId = ($this->institutionRepository ?? new InstitutionRepository())->createUnidade([
                    'orgao_id' => $orgaoId,
                    'codigo_unidade' => 'SEDE',
                    'nome_unidade' => $data['nome_unidade'],
                    'tipo_unidade' => 'SEDE',
                    'uf_sigla' => $data['uf_sigla'],
                    'status_unidade' => 'ATIVA',
                ]);

                $usuarioId = ($this->institutionRepository ?? new InstitutionRepository())->createUsuario([
                    'conta_id' => $contaId,
                    'orgao_id' => $orgaoId,
                    'unidade_id' => $unidadeId,
                    'uf_sigla' => $data['uf_sigla'],
                    'nome_completo' => $data['nome_responsavel'],
                    'email_login' => $data['email_login'],
                    'matricula_funcional' => null,
                    'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'status_usuario' => 'ATIVO',
                ]);
            }

            $this->attachProfile($usuarioId, UserProfile::GESTOR);

            $startDate = (new DateTimeImmutable('today'))->format('Y-m-d');
            $assinaturaId = ($this->commercialRepository ?? new CommercialRepository())->createAssinatura([
                'conta_id' => $contaId,
                'uf_sigla' => $data['uf_sigla'],
                'plano_id' => (int) $selection['id'],
                'status_assinatura' => 'SUSPENSA',
                'inicia_em' => $startDate,
                'expira_em' => null,
                'trial_fim_em' => null,
                'motivo_status' => self::PAYMENT_PENDING_REASON,
            ]);

            $faturaId = ($this->billingRepository ?? new BillingRepository())->createInvoice([
                'conta_id' => $contaId,
                'assinatura_id' => $assinaturaId,
                'plano_id' => (int) $selection['id'],
                'uf_sigla' => $data['uf_sigla'],
                'ciclo_cobranca' => $data['billing_cycle'],
                'moeda' => (string) config('payments.currency', 'BRL'),
                'valor_bruto' => number_format((float) (($selection['selection']['amount_gross'] ?? 0.0)), 2, '.', ''),
                'desconto_valor' => number_format((float) (($selection['selection']['discount_value'] ?? 0.0)), 2, '.', ''),
                'valor_liquido' => number_format((float) (($selection['selection']['amount_net'] ?? 0.0)), 2, '.', ''),
                'status_fatura' => 'ABERTA',
                'vence_em' => (new DateTimeImmutable('today'))->modify('+3 days')->format('Y-m-d'),
                'referencia_externa' => null,
                'observacao' => 'Criada via onboarding publico',
            ]);

            $checkoutTokenPlain = $this->newCheckoutToken();
            $checkoutTokenHash = $this->checkoutTokenHash($checkoutTokenPlain);
            $checkoutTokenPrefix = strtoupper(substr($checkoutTokenHash, 0, 12));

            $paymentId = ($this->billingRepository ?? new BillingRepository())->createPayment([
                'conta_id' => $contaId,
                'assinatura_id' => $assinaturaId,
                'fatura_id' => $faturaId,
                'uf_sigla' => $data['uf_sigla'],
                'gateway' => 'MERCADO_PAGO',
                'status_pagamento' => 'PENDENTE',
                'checkout_token_prefix' => $checkoutTokenPrefix,
                'checkout_token_hash' => $checkoutTokenHash,
                'gateway_referencia' => null,
                'checkout_url' => null,
                'moeda' => (string) config('payments.currency', 'BRL'),
                'valor_liquido' => number_format((float) (($selection['selection']['amount_net'] ?? 0.0)), 2, '.', ''),
                'response_excerpt' => null,
                'payload' => [
                    'plan_code' => $selection['codigo_plano'] ?? null,
                    'billing_cycle' => $data['billing_cycle'],
                ],
            ]);

            $checkout = ($this->mercadoPagoGatewayService ?? new MercadoPagoGatewayService())->createCheckoutPreference([
                'external_reference' => $checkoutTokenPlain,
                'item_id' => 'SIGERD_' . strtoupper((string) ($selection['codigo_plano'] ?? 'PLANO')),
                'item_title' => (string) ($selection['nome_plano'] ?? 'Assinatura SIGERD'),
                'item_description' => 'Assinatura ' . $data['billing_cycle'] . ' do plano SIGERD',
                'item_quantity' => 1,
                'currency' => (string) config('payments.currency', 'BRL'),
                'unit_price' => (float) (($selection['selection']['amount_net'] ?? 0.0)),
                'payer_name' => $data['nome_responsavel'],
                'payer_email' => $data['email_login'],
                'conta_id' => $contaId,
                'assinatura_id' => $assinaturaId,
                'fatura_id' => $faturaId,
                'success_url' => url('/checkout?token=' . rawurlencode($checkoutTokenPlain)),
                'failure_url' => url('/checkout?token=' . rawurlencode($checkoutTokenPlain)),
                'pending_url' => url('/checkout?token=' . rawurlencode($checkoutTokenPlain)),
                'notification_url' => trim((string) config('payments.mercadopago.webhook_url', '')),
            ]);

            $checkoutMode = strtolower((string) ($checkout['mode'] ?? ''));
            if ($checkoutMode === 'error' || (string) ($checkout['checkout_url'] ?? '') === '') {
                throw new \RuntimeException((string) ($checkout['message'] ?? 'Falha ao iniciar checkout Mercado Pago.'));
            }

            ($this->billingRepository ?? new BillingRepository())->updatePaymentCheckoutMeta($paymentId, [
                'gateway_referencia' => $checkout['gateway_reference'] ?? null,
                'checkout_url' => $checkout['checkout_url'] ?? null,
                'response_excerpt' => (string) ($checkout['message'] ?? ''),
                'payload' => [
                    'mode' => $checkout['mode'] ?? null,
                    'gateway_payload' => $checkout['payload'] ?? null,
                ],
            ]);

            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            $this->audit('PAYMENTS', 'PUBLIC_SUBSCRIPTION_STARTED', 'assinaturas', $assinaturaId, [
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $unidadeId,
                'usuario_id' => $usuarioId,
                'uf_sigla' => $data['uf_sigla'],
                'municipio_nome' => $data['municipio_nome'],
                'plan_code' => $selection['codigo_plano'] ?? null,
                'billing_cycle' => $data['billing_cycle'],
                'payment_mode' => $checkout['mode'] ?? null,
            ], $request);

            return [
                'ok' => true,
                'message' => 'Cadastro concluido. Finalize o pagamento para ativar o acesso.',
                'checkout_token' => $checkoutTokenPlain,
                'checkout_url' => $checkout['checkout_url'] ?? url('/checkout?token=' . rawurlencode($checkoutTokenPlain)),
                'payment_mode' => $checkout['mode'] ?? 'fallback_local',
            ];
        } catch (Throwable $exception) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Logger::error('public_onboarding', 'Falha ao iniciar assinatura publica.', [
                'error' => $exception->getMessage(),
                'email_login' => $data['email_login'],
                'uf_sigla' => $data['uf_sigla'],
                'plan_code' => $data['plan_code'],
                'billing_cycle' => $data['billing_cycle'],
            ]);

            return [
                'ok' => false,
                'message' => 'Nao foi possivel iniciar a assinatura agora. Tente novamente em instantes.',
            ];
        }
    }

    public function checkoutSummary(string $checkoutToken): array
    {
        $token = trim($checkoutToken);
        if ($token === '') {
            return ['ok' => false, 'message' => 'Token de checkout nao informado.'];
        }

        $payment = ($this->billingRepository ?? new BillingRepository())
            ->paymentByCheckoutTokenHash($this->checkoutTokenHash($token));

        if ($payment === null) {
            return ['ok' => false, 'message' => 'Checkout nao encontrado ou expirado.'];
        }

        $checkoutMode = strtolower(trim((string) ($payment['checkout_mode'] ?? '')));
        if ($checkoutMode === '') {
            $checkoutMode = $this->inferCheckoutMode(
                (string) ($payment['checkout_url'] ?? ''),
                (string) ($payment['gateway_referencia'] ?? '')
            );
        }

        return [
            'ok' => true,
            'data' => [
                'checkout_token' => $token,
                'pagamento_id' => (int) ($payment['pagamento_id'] ?? 0),
                'status_pagamento' => (string) ($payment['status_pagamento'] ?? 'PENDENTE'),
                'gateway' => (string) ($payment['gateway'] ?? 'MERCADO_PAGO'),
                'checkout_mode' => $checkoutMode,
                'checkout_url' => (string) ($payment['checkout_url'] ?? ''),
                'gateway_referencia' => (string) ($payment['gateway_referencia'] ?? ''),
                'fatura_id' => (int) ($payment['fatura_id'] ?? 0),
                'status_fatura' => (string) ($payment['status_fatura'] ?? 'ABERTA'),
                'ciclo_cobranca' => (string) ($payment['ciclo_cobranca'] ?? 'MENSAL'),
                'valor_liquido' => (float) ($payment['fatura_valor_liquido'] ?? 0.0),
                'valor_bruto' => (float) ($payment['valor_bruto'] ?? 0.0),
                'desconto_valor' => (float) ($payment['desconto_valor'] ?? 0.0),
                'moeda' => (string) ($payment['fatura_moeda'] ?? config('payments.currency', 'BRL')),
                'vence_em' => (string) ($payment['vence_em'] ?? ''),
                'assinatura_id' => (int) ($payment['assinatura_id'] ?? 0),
                'status_assinatura' => (string) ($payment['status_assinatura'] ?? 'SUSPENSA'),
                'plano_codigo' => (string) ($payment['codigo_plano'] ?? ''),
                'plano_nome' => (string) ($payment['nome_plano'] ?? ''),
                'email_login' => (string) ($payment['email_login'] ?? ''),
                'nome_responsavel' => (string) ($payment['nome_completo'] ?? ''),
                'usuario_id' => isset($payment['usuario_id']) ? (int) $payment['usuario_id'] : null,
            ],
        ];
    }

    public function confirmCheckout(string $checkoutToken, Request $request, string $source = 'MANUAL'): array
    {
        $summary = $this->checkoutSummary($checkoutToken);
        if (!($summary['ok'] ?? false)) {
            return $summary;
        }

        $data = is_array($summary['data'] ?? null) ? $summary['data'] : [];
        $statusPagamento = strtoupper((string) ($data['status_pagamento'] ?? ''));
        $checkoutMode = strtolower((string) ($data['checkout_mode'] ?? 'mercado_pago'));

        if ($checkoutMode === 'mercado_pago' && strtoupper($source) === 'MANUAL') {
            return [
                'ok' => false,
                'message' => 'Confirmacao manual desabilitada para checkout Mercado Pago. Aguardando retorno/webhook de pagamento.',
            ];
        }

        if ($statusPagamento === 'APROVADO') {
            return [
                'ok' => true,
                'already_processed' => true,
                'message' => 'Pagamento ja aprovado anteriormente. Acesse a plataforma com seu login.',
                'email_login' => (string) ($data['email_login'] ?? ''),
            ];
        }

        if (!in_array($statusPagamento, ['PENDENTE', 'ERRO'], true)) {
            return [
                'ok' => false,
                'message' => 'Pagamento nao pode ser confirmado a partir do status atual: ' . $statusPagamento,
            ];
        }

        $pdo = ($this->billingRepository ?? new BillingRepository())->pdo();

        $ownsTransaction = !$pdo->inTransaction();

        try {
            if ($ownsTransaction) {
                $pdo->beginTransaction();
            }

            ($this->billingRepository ?? new BillingRepository())->markPaymentStatus((int) ($data['pagamento_id'] ?? 0), 'APROVADO', [
                'gateway_referencia' => (string) ($data['gateway_referencia'] ?? ''),
                'response_excerpt' => 'Pagamento aprovado via fluxo ' . strtoupper($source),
                'payload' => ['source' => strtoupper($source)],
            ]);

            ($this->billingRepository ?? new BillingRepository())->markInvoicePaid((int) ($data['fatura_id'] ?? 0), (string) ($data['gateway_referencia'] ?? null));

            $today = new DateTimeImmutable('today');
            $expiresAt = strtoupper((string) ($data['ciclo_cobranca'] ?? 'MENSAL')) === 'ANUAL'
                ? $today->modify('+1 year')
                : $today->modify('+1 month');

            ($this->billingRepository ?? new BillingRepository())->markAssinaturaAtiva(
                (int) ($data['assinatura_id'] ?? 0),
                $today->format('Y-m-d'),
                $expiresAt->format('Y-m-d')
            );

            $this->activateModulesForPlan(
                (int) ($data['assinatura_id'] ?? 0),
                (string) ($data['plano_codigo'] ?? '')
            );

            $usuarioId = isset($data['usuario_id']) ? (int) $data['usuario_id'] : 0;
            if ($usuarioId > 0) {
                $this->attachProfile($usuarioId, UserProfile::GESTOR);
            }

            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }

            $this->audit('PAYMENTS', 'PUBLIC_PAYMENT_APPROVED', 'assinaturas', (int) ($data['assinatura_id'] ?? 0), [
                'pagamento_id' => (int) ($data['pagamento_id'] ?? 0),
                'fatura_id' => (int) ($data['fatura_id'] ?? 0),
                'plan_code' => (string) ($data['plano_codigo'] ?? ''),
                'billing_cycle' => (string) ($data['ciclo_cobranca'] ?? ''),
                'source' => strtoupper($source),
            ], $request);

            return [
                'ok' => true,
                'message' => 'Pagamento confirmado. Assinatura ativada com sucesso.',
                'email_login' => (string) ($data['email_login'] ?? ''),
            ];
        } catch (Throwable $exception) {
            if ($ownsTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            Logger::error('payments', 'Falha ao confirmar pagamento de checkout publico.', [
                'error' => $exception->getMessage(),
                'checkout_token_prefix' => substr($checkoutToken, 0, 10),
            ]);

            return [
                'ok' => false,
                'message' => 'Falha ao confirmar pagamento. Tente novamente em instantes.',
            ];
        }
    }

    public function processWebhook(array $payload, Request $request): array
    {
        $statusRaw = strtoupper(trim((string) ($payload['status'] ?? '')));
        $externalReference = trim((string) (
            $payload['external_reference']
            ?? $payload['transaction_token']
            ?? ($payload['data']['external_reference'] ?? '')
        ));

        if ($externalReference === '') {
            return ['ok' => false, 'status' => 422, 'message' => 'external_reference nao informado no webhook.'];
        }

        if (in_array($statusRaw, ['APPROVED', 'APROVADO', 'PAID', 'PAGO'], true)) {
            $result = $this->confirmCheckout($externalReference, $request, 'WEBHOOK');

            return [
                'ok' => (bool) ($result['ok'] ?? false),
                'status' => ($result['ok'] ?? false) ? 200 : 422,
                'message' => (string) ($result['message'] ?? 'Falha ao processar webhook de aprovacao.'),
            ];
        }

        $summary = $this->checkoutSummary($externalReference);
        if (!($summary['ok'] ?? false)) {
            return ['ok' => false, 'status' => 404, 'message' => 'Checkout de webhook nao localizado.'];
        }

        $data = is_array($summary['data'] ?? null) ? $summary['data'] : [];
        $mappedStatus = match ($statusRaw) {
            'REJECTED', 'RECUSADO' => 'RECUSADO',
            'CANCELLED', 'CANCELADO' => 'CANCELADO',
            'ERROR', 'ERRO' => 'ERRO',
            default => 'PENDENTE',
        };

        try {
            ($this->billingRepository ?? new BillingRepository())->markPaymentStatus((int) ($data['pagamento_id'] ?? 0), $mappedStatus, [
                'response_excerpt' => 'Webhook atualizado para status ' . $mappedStatus,
                'payload' => ['webhook_payload' => $payload],
            ]);

            if ($mappedStatus === 'CANCELADO') {
                ($this->billingRepository ?? new BillingRepository())->markInvoiceStatus((int) ($data['fatura_id'] ?? 0), 'CANCELADA', 'Pagamento cancelado no gateway');
            }

            $this->audit('PAYMENTS', 'PUBLIC_PAYMENT_WEBHOOK', 'assinaturas_pagamentos', (int) ($data['pagamento_id'] ?? 0), [
                'status_pagamento' => $mappedStatus,
                'pagamento_id' => (int) ($data['pagamento_id'] ?? 0),
                'fatura_id' => (int) ($data['fatura_id'] ?? 0),
            ], $request);

            return [
                'ok' => true,
                'status' => 200,
                'message' => 'Webhook processado com sucesso.',
            ];
        } catch (Throwable $exception) {
            Logger::error('payments', 'Falha ao processar webhook de pagamento.', [
                'error' => $exception->getMessage(),
                'checkout_token_prefix' => substr($externalReference, 0, 10),
            ]);

            return [
                'ok' => false,
                'status' => 500,
                'message' => 'Falha interna ao processar webhook.',
            ];
        }
    }

    private function sanitizeInput(array $input): array
    {
        return [
            'plan_code' => strtoupper(trim((string) ($input['plan_code'] ?? $input['plano'] ?? ''))),
            'billing_cycle' => strtoupper(trim((string) ($input['billing_cycle'] ?? $input['ciclo'] ?? ''))),
            'nome_responsavel' => trim((string) ($input['nome_responsavel'] ?? '')),
            'email_login' => strtolower(trim((string) ($input['email_login'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'password_confirmation' => (string) ($input['password_confirmation'] ?? ''),
            'nome_conta' => trim((string) ($input['nome_conta'] ?? '')),
            'nome_orgao' => trim((string) ($input['nome_orgao'] ?? '')),
            'nome_unidade' => $this->nullableText($input['nome_unidade'] ?? null) ?? 'Unidade Sede',
            'uf_sigla' => BrazilUf::normalize($input['uf_sigla'] ?? null),
            'municipio_nome' => $this->nullableText($input['municipio_nome'] ?? null),
            'aceite_termos' => $this->toBool($input['aceite_termos'] ?? null),
        ];
    }

    private function validateInput(array $data): array
    {
        $errors = [];

        if ($data['plan_code'] === '') {
            $errors[] = 'Plano nao informado.';
        }

        if (!in_array($data['billing_cycle'], ['MENSAL', 'ANUAL'], true)) {
            $errors[] = 'Ciclo de cobranca invalido.';
        }

        if (strlen($data['nome_responsavel']) < 3) {
            $errors[] = 'Informe o nome do responsavel com no minimo 3 caracteres.';
        }

        if (!filter_var($data['email_login'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um email de login valido.';
        }

        $minLength = (int) config('auth.password_min_length', 8);
        if (strlen($data['password']) < $minLength) {
            $errors[] = "A senha deve ter no minimo {$minLength} caracteres.";
        }

        if ($data['password'] !== $data['password_confirmation']) {
            $errors[] = 'A confirmacao de senha nao confere.';
        }

        if (strlen($data['nome_conta']) < 3) {
            $errors[] = 'Informe o nome da instituicao/conta com no minimo 3 caracteres.';
        }

        if (strlen($data['nome_orgao']) < 3) {
            $errors[] = 'Informe o nome do orgao com no minimo 3 caracteres.';
        }

        if ($data['uf_sigla'] === null) {
            $errors[] = 'UF invalido.';
        } elseif (!($this->territoryRepository ?? new TerritoryRepository())->ufExists($data['uf_sigla'])) {
            $errors[] = 'UF nao encontrada na base territorial.';
        }

        if (!$data['aceite_termos']) {
            $errors[] = 'Voce precisa aceitar os termos para continuar.';
        }

        return $errors;
    }

    private function attachProfile(int $userId, string $profileName): void
    {
        $profileId = ($this->institutionRepository ?? new InstitutionRepository())->profileIdByName($profileName);
        if ($profileId === null) {
            throw new \RuntimeException('Perfil obrigatorio nao encontrado: ' . $profileName);
        }

        ($this->institutionRepository ?? new InstitutionRepository())->vincularPerfilAoUsuario($userId, $profileId);
    }

    private function activateModulesForPlan(int $assinaturaId, string $planCode): void
    {
        $codes = $this->moduleCodesByPlan($planCode);
        $moduleIdsByCode = ($this->commercialRepository ?? new CommercialRepository())->moduleIdsByCodes($codes);

        foreach ($codes as $code) {
            $moduleId = (int) ($moduleIdsByCode[$code] ?? 0);
            if ($moduleId < 1) {
                continue;
            }

            ($this->commercialRepository ?? new CommercialRepository())->liberarModulo($assinaturaId, $moduleId, 'ATIVA');
        }
    }

    private function moduleCodesByPlan(string $planCode): array
    {
        $normalized = strtoupper(trim($planCode));

        return match ($normalized) {
            'START' => [
                'PUBLIC',
                'AUTH',
                'ADMIN',
                'OPERATIONAL',
                'AUDIT',
            ],
            'PRO' => [
                'PUBLIC',
                'AUTH',
                'ADMIN',
                'OPERATIONAL',
                'AUDIT',
                'PLANCON',
                'DISASTER_EXPANSION',
                'INTELLIGENCE',
                'DOCUMENTS',
                'GOVERNANCE',
                'ADV_REPORTS',
            ],
            'ENTERPRISE' => [
                'PUBLIC',
                'AUTH',
                'ADMIN',
                'OPERATIONAL',
                'AUDIT',
                'PLANCON',
                'DISASTER_EXPANSION',
                'INTELLIGENCE',
                'DOCUMENTS',
                'GOVERNANCE',
                'ADV_REPORTS',
                'ENTERPRISE_CORE',
                'API_ENTERPRISE',
                'INTEGRACOES_EXTERNAS',
                'AUTOMACOES',
                'ANALYTICS_EXECUTIVO',
                'SLA_SUPORTE',
                'ASSINATURA_DIGITAL',
            ],
            default => [
                'PUBLIC',
                'AUTH',
                'OPERATIONAL',
            ],
        };
    }

    private function hasValidContract(?array $assinatura): bool
    {
        if ($assinatura === null) {
            return false;
        }

        $status = strtoupper((string) ($assinatura['status_assinatura'] ?? ''));
        if (!in_array($status, ['TRIAL', 'ATIVA'], true)) {
            return false;
        }

        $today = new DateTimeImmutable('today');
        $expiraEm = trim((string) ($assinatura['expira_em'] ?? ''));
        if ($expiraEm === '') {
            return true;
        }

        try {
            $expDate = new DateTimeImmutable($expiraEm);
        } catch (Throwable) {
            return false;
        }

        return $expDate >= $today;
    }

    private function buildOrgaoSigla(string $nome): ?string
    {
        $clean = preg_replace('/[^A-Za-z0-9 ]/', ' ', $nome) ?? $nome;
        $chunks = preg_split('/\s+/', trim($clean)) ?: [];
        if ($chunks === []) {
            return null;
        }

        $sigla = '';
        foreach ($chunks as $chunk) {
            if ($chunk === '') {
                continue;
            }

            $sigla .= strtoupper(substr($chunk, 0, 1));
            if (strlen($sigla) >= 8) {
                break;
            }
        }

        return $sigla !== '' ? $sigla : null;
    }

    private function newCheckoutToken(): string
    {
        return 'ck_' . bin2hex(random_bytes(24));
    }

    private function checkoutTokenHash(string $token): string
    {
        $pepper = (string) config('payments.checkout_token_pepper', '');
        return hash('sha256', $token . $pepper);
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 'on', 'sim', 'yes'], true);
    }

    private function inferCheckoutMode(string $checkoutUrl, string $gatewayReference): string
    {
        $checkoutUrl = trim($checkoutUrl);
        $gatewayReference = trim($gatewayReference);

        if ($gatewayReference !== '') {
            return 'mercado_pago';
        }

        if ($checkoutUrl !== '' && str_starts_with($checkoutUrl, '/checkout?token=')) {
            return 'fallback_local';
        }

        return 'mercado_pago';
    }

    private function audit(string $module, string $action, string $entityType, ?int $entityId, array $details, Request $request): void
    {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $details['conta_id'] ?? null,
            'orgao_id' => $details['orgao_id'] ?? null,
            'unidade_id' => $details['unidade_id'] ?? null,
            'usuario_id' => $details['usuario_id'] ?? null,
            'modulo_codigo' => $module,
            'acao' => $action,
            'resultado' => 'SUCESSO',
            'entidade_tipo' => $entityType,
            'entidade_id' => $entityId,
            'detalhes' => $details,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
