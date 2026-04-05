<?php

declare(strict_types=1);

use App\Controllers\Api\EnterpriseApiController;
use App\Middleware\AuthenticateApiKey;
use App\Repositories\Enterprise\EnterpriseRepository;
use App\Services\Enterprise\EnterpriseService;
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

function makeRequest(string $method, string $uri, array $query = [], array $body = [], array $server = []): Request
{
    return new Request(
        strtoupper($method),
        $uri,
        $query,
        $body,
        array_merge([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'phase5-integration-test',
        ], $server)
    );
}

function decodeJsonResponse(object $response): array
{
    ob_start();
    $response->send();
    $raw = (string) ob_get_clean();
    $payload = json_decode($raw, true);
    assertTrue(is_array($payload), 'Falha: resposta JSON invalida no endpoint enterprise.');

    return $payload;
}

$pdo = Database::connection();
$suffix = date('YmdHis') . '_' . random_int(100, 999);

try {
    $pdo->beginTransaction();

    $pdo->exec(
        "INSERT INTO contas (nome_fantasia, uf_sigla, status_cadastral, created_at, updated_at)
         VALUES ('Conta-SP-F5-{$suffix}', 'SP', 'ATIVA', NOW(), NOW())"
    );
    $contaSpId = (int) $pdo->lastInsertId();

    $pdo->exec(
        "INSERT INTO orgaos (conta_id, nome_oficial, uf_sigla, status_orgao, created_at, updated_at)
         VALUES ({$contaSpId}, 'Orgao-SP-F5-{$suffix}', 'SP', 'ATIVO', NOW(), NOW())"
    );
    $orgaoSpId = (int) $pdo->lastInsertId();

    $pdo->exec(
        "INSERT INTO unidades (orgao_id, codigo_unidade, nome_unidade, uf_sigla, status_unidade, created_at, updated_at)
         VALUES ({$orgaoSpId}, 'UNI-F5-SP-{$suffix}', 'Unidade-SP-F5-{$suffix}', 'SP', 'ATIVA', NOW(), NOW())"
    );
    $unidadeSpId = (int) $pdo->lastInsertId();

    $authNonMaster = [
        'usuario_id' => 1,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'uf_sigla' => 'TO',
        'perfis' => ['ADMIN_ORGAO'],
        'modulos_liberados' => [
            'ENTERPRISE_CORE',
            'API_ENTERPRISE',
            'INTEGRACOES_EXTERNAS',
            'AUTOMACOES',
            'ANALYTICS_EXECUTIVO',
            'SLA_SUPORTE',
            'ASSINATURA_DIGITAL',
        ],
    ];

    $enterpriseService = new EnterpriseService();
    $scopeCheck = $enterpriseService->dashboardData($authNonMaster, ['uf' => 'SP']);
    assertTrue(
        strtoupper((string) ($scopeCheck['current_uf_filter'] ?? '')) === 'TO',
        'Falha: dashboard enterprise permitiu filtro UF fora do contexto para perfil nao ADMIN_MASTER.'
    );

    $deniedByUf = $enterpriseService->createApiClientApp(
        $authNonMaster,
        [
            'conta_id' => $contaSpId,
            'orgao_id' => $orgaoSpId,
            'unidade_id' => $unidadeSpId,
            'nome_app' => 'F5-App-SP-Bloqueada',
            'escopos' => 'READ_EXEC_SUMMARY',
            'limite_rpm' => 120,
            'status_app' => 'ATIVA',
        ],
        makeRequest('POST', '/admin/enterprise/api-apps')
    );

    assertTrue(
        ($deniedByUf['ok'] ?? false) === false,
        'Falha: perfil nao ADMIN_MASTER conseguiu criar API client fora do UF de contexto.'
    );

    $allowedApiApp = $enterpriseService->createApiClientApp(
        $authNonMaster,
        [
            'conta_id' => 1,
            'orgao_id' => 1,
            'unidade_id' => 1,
            'nome_app' => 'F5-App-TO-' . $suffix,
            'escopos' => 'READ_EXEC_SUMMARY',
            'limite_rpm' => 200,
            'status_app' => 'ATIVA',
        ],
        makeRequest('POST', '/admin/enterprise/api-apps')
    );

    assertTrue(
        ($allowedApiApp['ok'] ?? false) === true && trim((string) ($allowedApiApp['token_plain'] ?? '')) !== '',
        'Falha: criacao de API client enterprise no UF de contexto nao retornou token valido.'
    );

    $validToken = (string) $allowedApiApp['token_plain'];

    $middleware = new AuthenticateApiKey();
    $apiController = new EnterpriseApiController();

    $_SESSION['api_auth'] = null;
    $responseValid = $middleware->handle(
        makeRequest('GET', '/api/enterprise/executivo', [
            'data_inicio' => date('Y-m-d', strtotime('-30 days')),
            'data_fim' => date('Y-m-d'),
            'persistir' => '1',
        ], [], [
            'HTTP_X_API_KEY' => $validToken,
        ]),
        static fn(Request $request) => $apiController->executiveSummary($request)
    );

    $payloadValid = decodeJsonResponse($responseValid);
    assertTrue(
        ($payloadValid['ok'] ?? false) === true
        && is_array($payloadValid['data']['summary'] ?? null)
        && array_key_exists('total_incidentes', $payloadValid['data']['summary']),
        'Falha: endpoint API enterprise nao retornou resumo executivo esperado para token valido.'
    );

    $responseInvalid = $middleware->handle(
        makeRequest('GET', '/api/enterprise/executivo', [], [], [
            'HTTP_X_API_KEY' => 'sigerd.invalid.token.' . $suffix,
        ]),
        static fn(Request $request) => $apiController->executiveSummary($request)
    );
    $payloadInvalid = decodeJsonResponse($responseInvalid);

    assertTrue(
        ($payloadInvalid['ok'] ?? true) === false,
        'Falha: token API invalido nao foi bloqueado pelo middleware enterprise.'
    );

    $tokenSp = 'sigerd.sp.' . strtolower($suffix) . '.token';
    $tokenSpHash = hash('sha256', $tokenSp . (string) config('enterprise.api_token_pepper', ''));

    $repo = new EnterpriseRepository();
    $repo->createApiClientApp([
        'conta_id' => $contaSpId,
        'orgao_id' => $orgaoSpId,
        'unidade_id' => $unidadeSpId,
        'nome_app' => 'F5-App-SP-Modulo-Inativo-' . $suffix,
        'token_prefix' => strtoupper(substr(hash('sha256', $suffix), 0, 12)),
        'token_hash' => $tokenSpHash,
        'escopos' => ['READ_EXEC_SUMMARY'],
        'limite_rpm' => 100,
        'status_app' => 'ATIVA',
        'expira_em' => null,
        'criado_por_usuario_id' => 1,
    ]);

    $responseNoModule = $middleware->handle(
        makeRequest('GET', '/api/enterprise/executivo', [], [], [
            'HTTP_X_API_KEY' => $tokenSp,
        ]),
        static fn(Request $request) => $apiController->executiveSummary($request)
    );
    $payloadNoModule = decodeJsonResponse($responseNoModule);

    assertTrue(
        ($payloadNoModule['ok'] ?? true) === false,
        'Falha: token API de conta sem modulo API_ENTERPRISE foi aceito indevidamente.'
    );

    $pdo->rollBack();

    echo "OK - testes de integracao fase 5 executados com sucesso.\n";
    exit(0);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'ERRO - ' . $exception->getMessage() . "\n");
    exit(1);
}
