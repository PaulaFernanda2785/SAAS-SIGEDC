<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Repositories\Reports\AdvancedReportRepository;
use App\Services\Institutional\ScopeService;
use App\Services\Reports\OperationalAdvancedReportService;
use App\Services\Reports\OperationalReportService;

final class OperationalReportExportService
{
    public function __construct(
        private readonly ?OperationalReportService $basicReportService = null,
        private readonly ?OperationalAdvancedReportService $advancedReportService = null,
        private readonly ?AdvancedReportRepository $advancedReportRepository = null,
        private readonly ?ScopeService $scopeService = null
    ) {
    }

    public function exportBasic(array $auth, array $filters, string $format): array
    {
        $normalizedFormat = $this->normalizeFormat($format);
        if ($normalizedFormat === null) {
            return ['ok' => false, 'message' => 'Formato de exportacao invalido. Use csv ou pdf.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para exportacao.'];
        }

        $report = ($this->basicReportService ?? new OperationalReportService())->basicReport($auth, $filters);
        $scope = is_array($report['scope'] ?? null) ? $report['scope'] : $scopeService->scopeFilter($auth);

        $generated = $normalizedFormat === 'csv'
            ? $this->generateBasicCsv($scope, $report)
            : $this->generateBasicPdf($scope, $report);

        if (($generated['ok'] ?? false) !== true) {
            return $generated;
        }

        return [
            'ok' => true,
            'file_path' => $generated['file_path'],
            'download_name' => $generated['download_name'],
            'mime_type' => $generated['mime_type'],
        ];
    }

    public function exportAdvanced(array $auth, array $filters, string $format): array
    {
        $normalizedFormat = $this->normalizeFormat($format);
        if ($normalizedFormat === null) {
            return ['ok' => false, 'message' => 'Formato de exportacao invalido. Use csv ou pdf.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para exportacao.'];
        }

        $report = ($this->advancedReportService ?? new OperationalAdvancedReportService())
            ->report($auth, $filters, false);
        $scope = is_array($report['scope'] ?? null) ? $report['scope'] : $scopeService->scopeFilter($auth);

        $generated = $normalizedFormat === 'csv'
            ? $this->generateAdvancedCsv($scope, $report)
            : $this->generateAdvancedPdf($scope, $report);

        if (($generated['ok'] ?? false) !== true) {
            return $generated;
        }

        ($this->advancedReportRepository ?? new AdvancedReportRepository())->registerExecution([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'tipo_relatorio' => $normalizedFormat === 'csv'
                ? 'OPERACIONAL_AVANCADO_EXPORT_CSV'
                : 'OPERACIONAL_AVANCADO_EXPORT_PDF',
            'filtros' => $report['filters'] ?? [],
            'status_execucao' => 'CONCLUIDO',
            'total_registros' => (int) ($report['total_records'] ?? 0),
            'arquivo_caminho' => $generated['relative_path'] ?? null,
        ]);

        return [
            'ok' => true,
            'file_path' => $generated['file_path'],
            'download_name' => $generated['download_name'],
            'mime_type' => $generated['mime_type'],
        ];
    }

    private function generateBasicCsv(array $scope, array $report): array
    {
        $relativePath = $this->buildExportRelativePath('basic', 'csv', $scope);
        $absolutePath = storage_path($relativePath);
        if (!$this->ensureDirectory(dirname($absolutePath))) {
            return ['ok' => false, 'message' => 'Falha ao preparar diretorio de exportacao.'];
        }

        $handle = fopen($absolutePath, 'wb');
        if ($handle === false) {
            return ['ok' => false, 'message' => 'Falha ao iniciar arquivo CSV de exportacao.'];
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['SIGERD', 'Relatorio Operacional Basico']);
        fputcsv($handle, ['Escopo', (string) ($scope['escopo_ativo'] ?? 'N/A')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Incidentes por status']);
        fputcsv($handle, ['Status', 'Total']);
        foreach (($report['status_summary'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['status_incidente'] ?? ''),
                (string) ($row['total'] ?? 0),
            ]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Registros por tipo']);
        fputcsv($handle, ['Tipo', 'Total']);
        foreach (($report['records_by_type'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['tipo_registro'] ?? ''),
                (string) ($row['total'] ?? 0),
            ]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Incidentes no periodo']);
        fputcsv($handle, ['Numero', 'Incidente', 'Status', 'Tipo', 'Registros', 'Ultimo periodo', 'Abertura']);
        foreach (($report['incidents'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['numero_ocorrencia'] ?? ''),
                (string) ($row['nome_incidente'] ?? ''),
                (string) ($row['status_incidente'] ?? ''),
                (string) ($row['tipo_ocorrencia'] ?? ''),
                (string) ($row['total_registros'] ?? 0),
                (string) ($row['ultimo_periodo'] ?? ''),
                (string) ($row['data_hora_abertura'] ?? ''),
            ]);
        }

        fclose($handle);

        return [
            'ok' => true,
            'file_path' => $absolutePath,
            'relative_path' => $relativePath,
            'download_name' => basename($absolutePath),
            'mime_type' => 'text/csv; charset=UTF-8',
        ];
    }

    private function generateBasicPdf(array $scope, array $report): array
    {
        $relativePath = $this->buildExportRelativePath('basic', 'pdf', $scope);
        $absolutePath = storage_path($relativePath);
        if (!$this->ensureDirectory(dirname($absolutePath))) {
            return ['ok' => false, 'message' => 'Falha ao preparar diretorio de exportacao.'];
        }

        $lines = [];
        $lines[] = 'Escopo: ' . (string) ($scope['escopo_ativo'] ?? 'N/A');
        $lines[] = 'Incidentes filtrados: ' . count($report['incidents'] ?? []);
        $lines[] = 'Registros filtrados: ' . count($report['recent_records'] ?? []);
        $lines[] = '';
        $lines[] = 'Incidentes por status:';
        foreach (($report['status_summary'] ?? []) as $row) {
            $lines[] = sprintf(
                '%s: %s',
                (string) ($row['status_incidente'] ?? 'N/A'),
                (string) ($row['total'] ?? 0)
            );
        }
        $lines[] = '';
        $lines[] = 'Top incidentes no periodo:';
        $incidents = $report['incidents'] ?? [];
        foreach (array_slice(is_array($incidents) ? $incidents : [], 0, 12) as $row) {
            $lines[] = sprintf(
                '%s | %s | %s',
                (string) ($row['numero_ocorrencia'] ?? '-'),
                (string) ($row['nome_incidente'] ?? '-'),
                (string) ($row['status_incidente'] ?? '-')
            );
        }

        $pdfContent = $this->buildSimplePdfDocument('Relatorio Operacional Basico', $lines);
        if (file_put_contents($absolutePath, $pdfContent) === false) {
            return ['ok' => false, 'message' => 'Falha ao gravar arquivo PDF de exportacao.'];
        }

        return [
            'ok' => true,
            'file_path' => $absolutePath,
            'relative_path' => $relativePath,
            'download_name' => basename($absolutePath),
            'mime_type' => 'application/pdf',
        ];
    }

    private function generateAdvancedCsv(array $scope, array $report): array
    {
        $relativePath = $this->buildExportRelativePath('advanced', 'csv', $scope);
        $absolutePath = storage_path($relativePath);
        if (!$this->ensureDirectory(dirname($absolutePath))) {
            return ['ok' => false, 'message' => 'Falha ao preparar diretorio de exportacao.'];
        }

        $handle = fopen($absolutePath, 'wb');
        if ($handle === false) {
            return ['ok' => false, 'message' => 'Falha ao iniciar arquivo CSV de exportacao.'];
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['SIGERD', 'Relatorio Operacional Avancado']);
        fputcsv($handle, ['Escopo', (string) ($scope['escopo_ativo'] ?? 'N/A')]);
        fputcsv($handle, ['Total de blocos', (string) ($report['total_records'] ?? 0)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Tendencia diaria']);
        fputcsv($handle, ['Data', 'Total incidentes', 'Incidentes ativos']);
        foreach (($report['trend'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['referencia_data'] ?? ''),
                (string) ($row['total_incidentes'] ?? 0),
                (string) ($row['incidentes_ativos'] ?? 0),
            ]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Hotspots']);
        fputcsv($handle, ['Municipio', 'Total', 'Ativos']);
        foreach (($report['hotspots'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['municipio'] ?? ''),
                (string) ($row['total_incidentes'] ?? 0),
                (string) ($row['incidentes_ativos'] ?? 0),
            ]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Alertas ativos']);
        fputcsv($handle, ['Nivel', 'Codigo', 'Mensagem', 'Incidente', 'Gerado em']);
        foreach (($report['active_alerts'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['nivel_alerta'] ?? ''),
                (string) ($row['alerta_codigo'] ?? ''),
                (string) ($row['mensagem_alerta'] ?? ''),
                (string) ($row['numero_ocorrencia'] ?? ''),
                (string) ($row['gerado_em'] ?? ''),
            ]);
        }

        fclose($handle);

        return [
            'ok' => true,
            'file_path' => $absolutePath,
            'relative_path' => $relativePath,
            'download_name' => basename($absolutePath),
            'mime_type' => 'text/csv; charset=UTF-8',
        ];
    }

    private function generateAdvancedPdf(array $scope, array $report): array
    {
        $relativePath = $this->buildExportRelativePath('advanced', 'pdf', $scope);
        $absolutePath = storage_path($relativePath);
        if (!$this->ensureDirectory(dirname($absolutePath))) {
            return ['ok' => false, 'message' => 'Falha ao preparar diretorio de exportacao.'];
        }

        $lines = [];
        $lines[] = 'Escopo: ' . (string) ($scope['escopo_ativo'] ?? 'N/A');
        $lines[] = 'Total de blocos: ' . (string) ($report['total_records'] ?? 0);
        $lines[] = '';
        $lines[] = 'Hotspots prioritarios:';
        foreach (array_slice(is_array($report['hotspots'] ?? null) ? $report['hotspots'] : [], 0, 12) as $row) {
            $lines[] = sprintf(
                '%s | total=%s | ativos=%s',
                (string) ($row['municipio'] ?? 'N/A'),
                (string) ($row['total_incidentes'] ?? 0),
                (string) ($row['incidentes_ativos'] ?? 0)
            );
        }
        $lines[] = '';
        $lines[] = 'Alertas ativos:';
        foreach (array_slice(is_array($report['active_alerts'] ?? null) ? $report['active_alerts'] : [], 0, 15) as $row) {
            $lines[] = sprintf(
                '[%s] %s',
                (string) ($row['nivel_alerta'] ?? '-'),
                (string) ($row['mensagem_alerta'] ?? '-')
            );
        }

        $pdfContent = $this->buildSimplePdfDocument('Relatorio Operacional Avancado', $lines);
        if (file_put_contents($absolutePath, $pdfContent) === false) {
            return ['ok' => false, 'message' => 'Falha ao gravar arquivo PDF de exportacao.'];
        }

        return [
            'ok' => true,
            'file_path' => $absolutePath,
            'relative_path' => $relativePath,
            'download_name' => basename($absolutePath),
            'mime_type' => 'application/pdf',
        ];
    }

    private function normalizeFormat(string $format): ?string
    {
        $normalized = strtolower(trim($format));
        if ($normalized === 'csv' || $normalized === 'pdf') {
            return $normalized;
        }

        return null;
    }

    private function buildExportRelativePath(string $group, string $extension, array $scope): string
    {
        $contaId = max(0, (int) ($scope['conta_id'] ?? 0));
        $orgaoId = max(0, (int) ($scope['orgao_id'] ?? 0));
        $stamp = date('Ymd_His');
        $random = bin2hex(random_bytes(4));

        return sprintf(
            'exports/%s/%d/%d/relatorio_%s_%s.%s',
            $group,
            $contaId,
            $orgaoId,
            $stamp,
            $random,
            $extension
        );
    }

    private function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        return @mkdir($directory, 0775, true) || is_dir($directory);
    }

    private function buildSimplePdfDocument(string $title, array $lines): string
    {
        $safeLines = [];
        $safeLines[] = $title;
        foreach ($lines as $line) {
            $safeLines[] = (string) $line;
        }
        $safeLines = array_slice($safeLines, 0, 60);

        $stream = "BT\n/F1 12 Tf\n14 TL\n40 800 Td\n";
        foreach ($safeLines as $line) {
            $stream .= '(' . $this->escapePdfText($line) . ") Tj\nT*\n";
        }
        $stream .= "ET\n";

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj';
        $objects[] = '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[] = sprintf(
            "5 0 obj << /Length %d >> stream\n%sendstream endobj",
            strlen($stream),
            $stream
        );

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        $text = preg_replace('/[\x00-\x1F]/', ' ', $text) ?? '';

        return $text;
    }
}

