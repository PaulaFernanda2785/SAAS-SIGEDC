<?php

declare(strict_types=1);

use App\Repositories\Auth\UserRepository;
use App\Repositories\SaaS\CommercialRepository;
use App\Services\Auth\ContractAccessService;
use App\Services\SaaS\PublicOnboardingService;
use App\Support\Database;
use App\Support\Request;

require dirname(__DIR__, 2) . '/bootstrap/autoload.php';
require dirname(__DIR__, 2) . '/bootstrap/environment.php';
require dirname(__DIR__, 2) . '/bootstrap/session.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function makeRequest(string $method, string $uri, array $query = [], array $body = []): Request
{
    return new Request(
        strtoupper($method),
        $uri,
        $query,
        $body,
        [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'public-onboarding-integration-test',
        ]
    );
}

$pdo = Database::connection();
$suffix = date('YmdHis') . '_' . random_int(100, 999);

try {
    $pdo->beginTransaction();

    $service = new PublicOnboardingService();
    $userRepository = new UserRepository();
    $commercialRepository = new CommercialRepository();
    $contractAccessService = new ContractAccessService();

    $email = 'trial.' . strtolower($suffix) . '@sigerd.local';
    $password = 'Demo@12345';

    $trialResult = $service->startTrial([
        'plan_code' => 'START',
        'billing_cycle' => 'MENSAL',
        'nome_responsavel' => 'Responsavel Teste ' . $suffix,
        'email_login' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'nome_conta' => 'Conta Trial ' . $suffix,
        'nome_orgao' => 'Orgao Trial ' . $suffix,
        'nome_unidade' => 'Unidade Trial ' . $suffix,
        'uf_sigla' => 'TO',
        'municipio_nome' => 'Palmas',
        'aceite_termos' => '1',
    ], makeRequest('POST', '/demonstracao/trial'));

    assertTrue(($trialResult['ok'] ?? false) === true, 'Falha: nao foi possivel iniciar o trial publico de 3 dias.');

    $user = $userRepository->findByLogin($email);
    assertTrue($user !== null, 'Falha: usuario de trial nao foi criado.');

    $contaId = (int) ($user['conta_id'] ?? 0);
    assertTrue($contaId > 0, 'Falha: conta do usuario trial nao encontrada.');

    $assinatura = $commercialRepository->latestAssinaturaByConta($contaId);
    assertTrue(
        $assinatura !== null
        && strtoupper((string) ($assinatura['status_assinatura'] ?? '')) === 'TRIAL'
        && str_contains(strtoupper((string) ($assinatura['motivo_status'] ?? '')), 'TRIAL_DEMO_PUBLICO_3_DIAS'),
        'Falha: assinatura trial nao foi criada com motivo esperado.'
    );

    $contractTrial = $contractAccessService->evaluate($contaId);
    assertTrue(
        ($contractTrial['ok'] ?? false) === true,
        'Falha: contrato trial recem-criado nao foi reconhecido para autenticacao.'
    );

    $pdo->exec('UPDATE assinaturas SET expira_em = DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE id = ' . (int) ($assinatura['id'] ?? 0));

    $contractExpired = $contractAccessService->evaluate($contaId);
    assertTrue(
        ($contractExpired['ok'] ?? true) === false
        && (string) ($contractExpired['reason'] ?? '') === 'trial_demo_expirado',
        'Falha: trial expirado nao retornou bloqueio especifico de demonstracao.'
    );

    $subscriptionResult = $service->startDirectSubscription([
        'plan_code' => 'PRO',
        'billing_cycle' => 'ANUAL',
        'nome_responsavel' => 'Responsavel Teste ' . $suffix,
        'email_login' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'nome_conta' => 'Conta Trial ' . $suffix,
        'nome_orgao' => 'Orgao Trial ' . $suffix,
        'nome_unidade' => 'Unidade Trial ' . $suffix,
        'uf_sigla' => 'TO',
        'municipio_nome' => 'Palmas',
        'aceite_termos' => '1',
    ], makeRequest('POST', '/demonstracao/assinar'));

    assertTrue(
        ($subscriptionResult['ok'] ?? false) === true
        && trim((string) ($subscriptionResult['checkout_token'] ?? '')) !== '',
        'Falha: assinatura direta nao gerou checkout token valido.'
    );

    $checkoutToken = (string) $subscriptionResult['checkout_token'];
    $checkoutSummary = $service->checkoutSummary($checkoutToken);
    assertTrue(
        ($checkoutSummary['ok'] ?? false) === true
        && strtoupper((string) ($checkoutSummary['data']['status_pagamento'] ?? '')) === 'PENDENTE',
        'Falha: checkout nao ficou pendente apos abertura de assinatura direta.'
    );

    $confirmResult = $service->confirmCheckout($checkoutToken, makeRequest('POST', '/checkout/confirmar'), 'TESTE');
    assertTrue(
        ($confirmResult['ok'] ?? false) === true,
        'Falha: confirmacao de pagamento nao ativou assinatura.'
    );

    $contractAfterPayment = $contractAccessService->evaluate($contaId);
    assertTrue(
        ($contractAfterPayment['ok'] ?? false) === true
        && strtoupper((string) ($contractAfterPayment['assinatura']['status_assinatura'] ?? '')) === 'ATIVA',
        'Falha: contrato nao ficou ativo apos confirmacao de pagamento.'
    );

    $moduleCodes = $commercialRepository->moduleCodesByAssinatura((int) ($contractAfterPayment['assinatura']['id'] ?? 0));
    assertTrue(
        in_array('AUTH', $moduleCodes, true),
        'Falha: modulo AUTH nao foi liberado apos ativacao da assinatura paga.'
    );

    $pdo->rollBack();

    echo "OK - testes de integracao do onboarding publico executados com sucesso.\n";
    exit(0);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'ERRO - ' . $exception->getMessage() . "\n");
    exit(1);
}
