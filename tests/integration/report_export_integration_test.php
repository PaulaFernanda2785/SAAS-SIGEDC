<?php

declare(strict_types=1);

use App\Services\Export\OperationalReportExportService;
use App\Support\Database;

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
$filesToCleanup = [];

try {
    $pdo->beginTransaction();

    $service = new OperationalReportExportService();
    $auth = [
        'usuario_id' => 1,
        'conta_id' => 1,
        'orgao_id' => 1,
        'unidade_id' => 1,
        'uf_sigla' => 'TO',
        'escopos' => ['PROPRIA_UNIDADE'],
    ];

    $basic = $service->exportBasic($auth, [], 'csv');
    assertTrue(
        ($basic['ok'] ?? false) === true,
        'Falha: exportacao basica CSV nao foi concluida.'
    );
    $basicFile = (string) ($basic['file_path'] ?? '');
    assertTrue(
        $basicFile !== '' && is_file($basicFile),
        'Falha: arquivo de exportacao basica CSV nao foi gerado.'
    );
    $filesToCleanup[] = $basicFile;

    $advanced = $service->exportAdvanced($auth, [], 'csv');
    assertTrue(
        ($advanced['ok'] ?? false) === true,
        'Falha: exportacao avancada CSV nao foi concluida.'
    );
    $advancedFile = (string) ($advanced['file_path'] ?? '');
    assertTrue(
        $advancedFile !== '' && is_file($advancedFile),
        'Falha: arquivo de exportacao avancada CSV nao foi gerado.'
    );
    $filesToCleanup[] = $advancedFile;

    $count = (int) $pdo->query(
        "SELECT COUNT(*)
         FROM relatorios_avancados_execucoes
         WHERE tipo_relatorio = 'OPERACIONAL_AVANCADO_EXPORT_CSV'
           AND arquivo_caminho IS NOT NULL
           AND arquivo_caminho <> ''"
    )->fetchColumn();
    assertTrue(
        $count > 0,
        'Falha: persistencia de exportacao avancada nao foi registrada.'
    );

    $pdo->rollBack();
    foreach ($filesToCleanup as $file) {
        @unlink($file);
    }

    echo "OK - testes de integracao de exportacao executados com sucesso.\n";
    exit(0);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($filesToCleanup as $file) {
        @unlink($file);
    }

    fwrite(STDERR, "ERRO - " . $exception->getMessage() . "\n");
    exit(1);
}

