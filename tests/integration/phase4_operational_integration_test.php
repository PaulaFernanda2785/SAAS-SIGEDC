<?php

declare(strict_types=1);

use App\Services\Files\OperationalDocumentService;
use App\Services\Reports\OperationalIntelligenceService;
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

$pdo = Database::connection();
$suffix = date('YmdHis') . '_' . random_int(100, 999);
$tempRelativePath = 'attachments/1/1/phase4_test_' . $suffix . '.txt';
$tempAbsolutePath = storage_path($tempRelativePath);

try {
    $dir = dirname($tempAbsolutePath);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Falha ao preparar diretorio temporario de anexos para teste.');
    }

    if (file_put_contents($tempAbsolutePath, 'phase4-integration') === false) {
        throw new RuntimeException('Falha ao gravar arquivo temporario para teste de download.');
    }

    $pdo->beginTransaction();

    $pdo->exec(
        "INSERT INTO usuarios
            (conta_id, orgao_id, unidade_id, nome_completo, email_login, password_hash, status_usuario, created_at, updated_at)
         VALUES
            (1, 1, 1, 'Fase4 Tester {$suffix}', 'fase4_tester_{$suffix}@sigerd.local', 'hash', 'ATIVO', NOW(), NOW())"
    );
    $testerId = (int) $pdo->lastInsertId();

    $stmtAttachment = $pdo->prepare(
        'INSERT INTO anexos
            (conta_id, orgao_id, usuario_envio_id, entidade_tipo, entidade_id, arquivo_nome, arquivo_caminho, arquivo_mime, tamanho_bytes, hash_arquivo, visibilidade, created_at)
         VALUES
            (1, 1, 1, :entidade_tipo, :entidade_id, :arquivo_nome, :arquivo_caminho, :arquivo_mime, :tamanho_bytes, :hash_arquivo, :visibilidade, NOW())'
    );
    $stmtAttachment->execute([
        'entidade_tipo' => 'incidentes',
        'entidade_id' => 1,
        'arquivo_nome' => 'phase4-test.txt',
        'arquivo_caminho' => $tempRelativePath,
        'arquivo_mime' => 'text/plain',
        'tamanho_bytes' => filesize($tempAbsolutePath) ?: 0,
        'hash_arquivo' => hash_file('sha256', $tempAbsolutePath),
        'visibilidade' => 'PRIVADO',
    ]);
    $attachmentId = (int) $pdo->lastInsertId();

    $request = new Request(
        'GET',
        '/operational/documentos/download',
        ['anexo_id' => (string) $attachmentId],
        [],
        [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'phase4-integration-test',
        ]
    );

    $documentService = new OperationalDocumentService();
    $downloadDenied = $documentService->download([
        'usuario_id' => $testerId,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'perfis' => ['OPERADOR'],
        'escopos' => ['PROPRIA_UNIDADE'],
    ], $attachmentId, $request);

    assertTrue(
        ($downloadDenied['ok'] ?? false) === false,
        'Falha: perfil OPERADOR conseguiu baixar anexo PRIVADO de outro usuario.'
    );

    $downloadAllowed = $documentService->download([
        'usuario_id' => $testerId,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'perfis' => ['ANALISTA'],
        'escopos' => ['PROPRIA_UNIDADE'],
    ], $attachmentId, $request);

    assertTrue(
        ($downloadAllowed['ok'] ?? false) === true,
        'Falha: perfil ANALISTA nao conseguiu baixar anexo PRIVADO dentro do escopo.'
    );

    $incidentStmt = $pdo->prepare(
        'INSERT INTO incidentes
            (
                conta_id, orgao_id, unidade_id, numero_ocorrencia, nome_incidente, tipo_ocorrencia,
                data_hora_abertura, municipio, descricao_inicial, status_incidente, aberto_por_usuario_id, created_at, updated_at
            )
         VALUES
            (
                1, 1, 1, :numero_ocorrencia, :nome_incidente, :tipo_ocorrencia,
                NOW(), :municipio, :descricao_inicial, :status_incidente, 1, NOW(), NOW()
            )'
    );

    for ($i = 1; $i <= 6; $i++) {
        $incidentStmt->execute([
            'numero_ocorrencia' => 'PH4-' . $suffix . '-' . $i,
            'nome_incidente' => 'Incidente Fase4 ' . $i,
            'tipo_ocorrencia' => 'ENXURRADA',
            'municipio' => 'Palmas/TO',
            'descricao_inicial' => 'Teste de inteligencia operacional fase 4',
            'status_incidente' => 'ABERTO',
        ]);
    }

    $intelligenceService = new OperationalIntelligenceService();
    $dashboard = $intelligenceService->dashboard([
        'usuario_id' => $testerId,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'uf_sigla' => 'TO',
        'escopos' => ['PROPRIA_UNIDADE'],
    ], []);

    $alerts = is_array($dashboard['alerts'] ?? null) ? $dashboard['alerts'] : [];
    $codes = [];
    foreach ($alerts as $alert) {
        $codes[] = (string) ($alert['alerta_codigo'] ?? '');
    }

    assertTrue(
        in_array('HOTSPOT_CONCENTRACAO_ALTA', $codes, true),
        'Falha: motor de alertas nao gerou alerta de concentracao de hotspot.'
    );
    assertTrue(
        in_array('BRIEFING_INICIAL_AUSENTE', $codes, true),
        'Falha: motor de alertas nao gerou alerta de ausencia de briefing inicial.'
    );

    $countActiveAlerts = (int) $pdo
        ->query("SELECT COUNT(*) FROM inteligencia_alertas_operacionais WHERE conta_id = 1 AND orgao_id = 1 AND status_alerta = 'ATIVO'")
        ->fetchColumn();
    assertTrue(
        $countActiveAlerts >= 2,
        'Falha: alertas ativos nao foram persistidos em inteligencia_alertas_operacionais.'
    );

    $pdo->rollBack();
    @unlink($tempAbsolutePath);

    echo "OK - testes de integracao fase 4 executados com sucesso.\n";
    exit(0);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    @unlink($tempAbsolutePath);
    fwrite(STDERR, "ERRO - " . $exception->getMessage() . "\n");
    exit(1);
}

