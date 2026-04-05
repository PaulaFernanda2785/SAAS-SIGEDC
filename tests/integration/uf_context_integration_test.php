<?php

declare(strict_types=1);

use App\Controllers\Admin\CommercialController;
use App\Controllers\Admin\InstitutionController;
use App\Controllers\Api\TerritoryController;
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
            'HTTP_USER_AGENT' => 'integration-test',
        ]
    );
}

function decodeJsonResponse(object $response): array
{
    ob_start();
    $response->send();
    $raw = (string) ob_get_clean();
    $payload = json_decode($raw, true);
    assertTrue(is_array($payload), 'Falha: resposta JSON invalida da API territorial.');

    return $payload;
}

function clearTerritoryCache(): void
{
    $files = glob(storage_path('cache/territory/*.json'));
    if (!is_array($files)) {
        return;
    }

    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

function territoryCacheCount(): int
{
    $files = glob(storage_path('cache/territory/*.json'));
    return is_array($files) ? count($files) : 0;
}

$pdo = Database::connection();
$suffix = date('YmdHis') . '_' . random_int(100, 999);

try {
    $pdo->beginTransaction();

    $pdo->exec(
        "INSERT INTO contas (nome_fantasia, uf_sigla, status_cadastral, created_at, updated_at)
         VALUES ('Conta-SP-{$suffix}', 'SP', 'ATIVA', NOW(), NOW())"
    );
    $contaSpId = (int) $pdo->lastInsertId();

    $pdo->exec(
        "INSERT INTO orgaos (conta_id, nome_oficial, uf_sigla, status_orgao, created_at, updated_at)
         VALUES ({$contaSpId}, 'Orgao-SP-{$suffix}', 'SP', 'ATIVO', NOW(), NOW())"
    );
    $orgaoSpId = (int) $pdo->lastInsertId();

    $planoId = (int) $pdo->query("SELECT id FROM planos_catalogo ORDER BY id ASC LIMIT 1")->fetchColumn();
    if ($planoId < 1) {
        $pdo->exec(
            "INSERT INTO planos_catalogo
                (codigo_plano, nome_plano, descricao, preco_mensal, limite_usuarios, status_plano, created_at, updated_at)
             VALUES
                ('TEST_UF', 'Plano Teste UF', 'Plano para teste de integracao UF', 99.90, 10, 'ATIVO', NOW(), NOW())"
        );
        $planoId = (int) $pdo->lastInsertId();
    }

    $codigoMunicipioTeste = 9700000 + random_int(100, 999);
    $nomeMunicipioTeste = "Teste Cache {$suffix}";
    $stmtMunicipio = $pdo->prepare(
        "INSERT INTO territorios_municipios
            (codigo_ibge, uf_sigla, nome_municipio, latitude, longitude, regiao_codigo, regiao_nome, area_km2, created_at, updated_at)
         VALUES
            (:codigo_ibge, 'TO', :nome_municipio, NULL, NULL, 1, 'Norte', NULL, NOW(), NOW())
         ON DUPLICATE KEY UPDATE
            nome_municipio = VALUES(nome_municipio),
            updated_at = NOW()"
    );
    $stmtMunicipio->execute([
        'codigo_ibge' => $codigoMunicipioTeste,
        'nome_municipio' => $nomeMunicipioTeste,
    ]);

    $_SESSION['auth'] = [
        'usuario_id' => 1,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'uf_sigla' => 'TO',
        'perfis' => ['ADMIN_ORGAO'],
        'perfil_primario' => 'ADMIN_ORGAO',
        'area' => 'admin',
    ];

    $_SESSION['_flash']['next'] = [];
    $_SESSION['_flash']['current'] = [];

    $institutionController = new InstitutionController();

    $responseIndex = $institutionController->index(makeRequest('GET', '/admin/institucional', ['uf' => 'SP']));
    ob_start();
    $responseIndex->send();
    $html = (string) ob_get_clean();

    assertTrue(
        !str_contains($html, "Conta-SP-{$suffix}"),
        'Falha: perfil nao ADMIN_MASTER conseguiu consultar dados de outro UF no modulo institucional.'
    );

    $nomeContaBloqueada = "Conta-Bloqueada-SP-{$suffix}";
    $institutionController->storeAccount(makeRequest('POST', '/admin/institucional/contas', [], [
        'nome_fantasia' => $nomeContaBloqueada,
        'uf_sigla' => 'SP',
        'status_cadastral' => 'ATIVA',
    ]));

    $countContaUfSp = (int) $pdo
        ->query("SELECT COUNT(*) FROM contas WHERE nome_fantasia = '{$nomeContaBloqueada}' AND uf_sigla = 'SP'")
        ->fetchColumn();
    assertTrue(
        $countContaUfSp === 0,
        'Falha: perfil nao ADMIN_MASTER conseguiu gravar conta fora do UF de contexto.'
    );

    $countContaUfTo = (int) $pdo
        ->query("SELECT COUNT(*) FROM contas WHERE nome_fantasia = '{$nomeContaBloqueada}' AND uf_sigla = 'TO'")
        ->fetchColumn();
    assertTrue(
        $countContaUfTo === 1,
        'Falha: criacao de conta nao respeitou heranca de UF de contexto para perfil nao ADMIN_MASTER.'
    );

    $commercialController = new CommercialController();
    $responseCommercial = $commercialController->index(makeRequest('GET', '/admin/comercial', ['uf' => 'SP']));
    ob_start();
    $responseCommercial->send();
    $commercialHtml = (string) ob_get_clean();

    assertTrue(
        !str_contains($commercialHtml, "Conta-SP-{$suffix}"),
        'Falha: perfil nao ADMIN_MASTER conseguiu consultar assinaturas/contas de outro UF no modulo comercial.'
    );

    $territoryController = new TerritoryController();
    $ufsRestrictedPayload = decodeJsonResponse(
        $territoryController->ufs(makeRequest('GET', '/api/territorios/ufs'))
    );
    $ufsRestrictedRows = is_array($ufsRestrictedPayload['data'] ?? null) ? $ufsRestrictedPayload['data'] : [];
    assertTrue(
        ($ufsRestrictedPayload['ok'] ?? null) === true
        && count($ufsRestrictedRows) === 1
        && strtoupper((string) ($ufsRestrictedRows[0]['sigla'] ?? '')) === 'TO',
        'Falha: endpoint /api/territorios/ufs nao restringiu retorno ao UF de contexto para perfil nao ADMIN_MASTER.'
    );

    $responseApi = $territoryController->municipios(makeRequest('GET', '/api/territorios/municipios', [
        'uf' => 'SP',
        'q' => 'Sao',
    ]));
    $apiPayload = decodeJsonResponse($responseApi);

    assertTrue(
        is_array($apiPayload) && ($apiPayload['ok'] ?? null) === false,
        'Falha: perfil nao ADMIN_MASTER recebeu resposta valida de bloqueio na API territorial fora do UF de contexto.'
    );

    $commercialController->storeAssinatura(makeRequest('POST', '/admin/comercial/assinaturas', [], [
        'conta_id' => (string) $contaSpId,
        'plano_id' => (string) $planoId,
        'status_assinatura' => 'TRIAL',
        'inicia_em' => date('Y-m-d'),
        'motivo_status' => "TEST_UF_BLOCK_{$suffix}",
    ]));

    $countAssinaturaBloqueada = (int) $pdo
        ->query("SELECT COUNT(*) FROM assinaturas WHERE conta_id = {$contaSpId} AND motivo_status = 'TEST_UF_BLOCK_{$suffix}'")
        ->fetchColumn();
    assertTrue(
        $countAssinaturaBloqueada === 0,
        'Falha: perfil nao ADMIN_MASTER conseguiu gravar assinatura fora do UF de contexto.'
    );

    $_SESSION['auth'] = [
        'usuario_id' => 1,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'uf_sigla' => 'TO',
        'perfis' => ['ADMIN_MASTER'],
        'perfil_primario' => 'ADMIN_MASTER',
        'area' => 'admin',
    ];

    clearTerritoryCache();
    $cacheBefore = territoryCacheCount();

    $ufsAdminPayload = decodeJsonResponse(
        $territoryController->ufs(makeRequest('GET', '/api/territorios/ufs'))
    );
    $ufsAdminRows = is_array($ufsAdminPayload['data'] ?? null) ? $ufsAdminPayload['data'] : [];
    $hasTo = false;
    $hasSp = false;
    foreach ($ufsAdminRows as $uf) {
        $sigla = strtoupper((string) ($uf['sigla'] ?? ''));
        if ($sigla === 'TO') {
            $hasTo = true;
        }
        if ($sigla === 'SP') {
            $hasSp = true;
        }
    }

    assertTrue(
        ($ufsAdminPayload['ok'] ?? null) === true && $hasTo && $hasSp,
        'Falha: endpoint /api/territorios/ufs nao retornou visao ampla para ADMIN_MASTER.'
    );

    $municipiosAdminPayloadOne = decodeJsonResponse(
        $territoryController->municipios(makeRequest('GET', '/api/territorios/municipios', [
            'uf' => 'TO',
            'q' => 'Tes',
            'limit' => '25',
        ]))
    );
    $rowsOne = is_array($municipiosAdminPayloadOne['data'] ?? null) ? $municipiosAdminPayloadOne['data'] : [];
    $foundInsertedMunicipio = false;
    foreach ($rowsOne as $row) {
        if (stripos((string) ($row['nome_municipio'] ?? ''), 'Teste Cache') === 0) {
            $foundInsertedMunicipio = true;
            break;
        }
    }
    assertTrue(
        ($municipiosAdminPayloadOne['ok'] ?? null) === true && $foundInsertedMunicipio,
        'Falha: ADMIN_MASTER nao obteve municipio esperado no autocomplete por UF.'
    );

    $cacheAfterFirstLookup = territoryCacheCount();
    assertTrue(
        $cacheAfterFirstLookup > $cacheBefore,
        'Falha: cache de autocomplete territorial nao foi criado na primeira consulta.'
    );

    $municipiosAdminPayloadTwo = decodeJsonResponse(
        $territoryController->municipios(makeRequest('GET', '/api/territorios/municipios', [
            'uf' => 'TO',
            'q' => 'Teste C',
            'limit' => '25',
        ]))
    );
    $cacheAfterSecondLookup = territoryCacheCount();
    assertTrue(
        ($municipiosAdminPayloadTwo['ok'] ?? null) === true
        && $cacheAfterSecondLookup === $cacheAfterFirstLookup,
        'Falha: cache por UF+prefixo nao foi reaproveitado em consultas subsequentes.'
    );

    $pdo->rollBack();

    echo "OK - testes de integracao UF executados com sucesso.\n";
    exit(0);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "ERRO - " . $exception->getMessage() . "\n");
    exit(1);
}
