<?php

declare(strict_types=1);

use App\Support\Database;

require dirname(__DIR__) . '/bootstrap/autoload.php';
require dirname(__DIR__) . '/bootstrap/environment.php';

$territoriosPath = base_path('territorios');
if (!is_dir($territoriosPath)) {
    fwrite(STDERR, "Diretorio 'territorios' nao encontrado.\n");
    exit(1);
}

$ufFilter = null;
foreach ($argv ?? [] as $arg) {
    if (str_starts_with((string) $arg, '--uf=')) {
        $candidate = strtoupper(trim(substr((string) $arg, 5)));
        if (strlen($candidate) === 2) {
            $ufFilter = $candidate;
        }
    }
}

$pdo = Database::connection();

$ufStatement = $pdo->prepare(
    'INSERT INTO territorios_ufs (codigo_ibge, sigla, nome, regiao_codigo, regiao_nome, created_at, updated_at)
     VALUES (:codigo_ibge, :sigla, :nome, :regiao_codigo, :regiao_nome, NOW(), NOW())
     ON DUPLICATE KEY UPDATE
        nome = VALUES(nome),
        regiao_codigo = VALUES(regiao_codigo),
        regiao_nome = VALUES(regiao_nome),
        updated_at = NOW()'
);

$municipioStatement = $pdo->prepare(
    'INSERT INTO territorios_municipios
        (codigo_ibge, uf_sigla, nome_municipio, latitude, longitude, regiao_codigo, regiao_nome, area_km2, created_at, updated_at)
     VALUES
        (:codigo_ibge, :uf_sigla, :nome_municipio, :latitude, :longitude, :regiao_codigo, :regiao_nome, :area_km2, NOW(), NOW())
     ON DUPLICATE KEY UPDATE
        uf_sigla = VALUES(uf_sigla),
        nome_municipio = VALUES(nome_municipio),
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        regiao_codigo = VALUES(regiao_codigo),
        regiao_nome = VALUES(regiao_nome),
        area_km2 = VALUES(area_km2),
        updated_at = NOW()'
);

$csvFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($territoriosPath, FilesystemIterator::SKIP_DOTS)
);
foreach ($iterator as $fileInfo) {
    if (!$fileInfo instanceof SplFileInfo || !$fileInfo->isFile()) {
        continue;
    }

    $filename = strtolower($fileInfo->getFilename());
    if (str_ends_with($filename, '_municipios_com_geolocalizacao.csv')) {
        if ($ufFilter !== null) {
            $folderName = strtoupper((string) basename((string) $fileInfo->getPath()));
            if (!str_starts_with($folderName, $ufFilter . '_')) {
                continue;
            }
        }

        $csvFiles[] = $fileInfo->getPathname();
    }
}

if ($csvFiles === []) {
    $extra = $ufFilter !== null ? " para UF {$ufFilter}" : '';
    fwrite(STDERR, "Nenhum CSV territorial encontrado em 'territorios/*_Municipios_2025'{$extra}.\n");
    exit(1);
}

$totalMunicipios = 0;
$totalArquivos = 0;

try {
    $pdo->beginTransaction();

    foreach ($csvFiles as $csvFile) {
        $handle = fopen($csvFile, 'rb');
        if ($handle === false) {
            continue;
        }

        $header = fgetcsv($handle, 0, ',');
        if (!is_array($header)) {
            fclose($handle);
            continue;
        }

        $header = array_map(static fn(string $column): string => normalizeHeader($column), $header);
        $index = array_flip($header);
        $required = ['CD_MUN', 'NM_MUN', 'CD_UF', 'NM_UF', 'SIGLA_UF', 'CD_REGIAO', 'NM_REGIAO', 'AREA_KM2', 'LATITUDE', 'LONGITUDE'];
        $isValid = true;
        foreach ($required as $requiredColumn) {
            if (!array_key_exists($requiredColumn, $index)) {
                $isValid = false;
                break;
            }
        }

        if (!$isValid) {
            fclose($handle);
            continue;
        }

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (!is_array($row) || count($row) < count($header)) {
                continue;
            }

            $codigoUf = toInt($row[$index['CD_UF']] ?? null);
            $siglaUf = normalizeUf($row[$index['SIGLA_UF']] ?? null);
            $nomeUf = normalizeText($row[$index['NM_UF']] ?? null);
            if ($codigoUf === null || $siglaUf === null || $nomeUf === null) {
                continue;
            }

            $ufStatement->execute([
                'codigo_ibge' => $codigoUf,
                'sigla' => $siglaUf,
                'nome' => $nomeUf,
                'regiao_codigo' => toInt($row[$index['CD_REGIAO']] ?? null),
                'regiao_nome' => normalizeText($row[$index['NM_REGIAO']] ?? null),
            ]);

            $codigoMunicipio = toInt($row[$index['CD_MUN']] ?? null);
            $nomeMunicipio = normalizeText($row[$index['NM_MUN']] ?? null);
            if ($codigoMunicipio === null || $nomeMunicipio === null) {
                continue;
            }

            $municipioStatement->execute([
                'codigo_ibge' => $codigoMunicipio,
                'uf_sigla' => $siglaUf,
                'nome_municipio' => $nomeMunicipio,
                'latitude' => toDecimal($row[$index['LATITUDE']] ?? null),
                'longitude' => toDecimal($row[$index['LONGITUDE']] ?? null),
                'regiao_codigo' => toInt($row[$index['CD_REGIAO']] ?? null),
                'regiao_nome' => normalizeText($row[$index['NM_REGIAO']] ?? null),
                'area_km2' => toDecimal($row[$index['AREA_KM2']] ?? null),
            ]);

            $totalMunicipios++;
        }

        fclose($handle);
        $totalArquivos++;
    }

    $pdo->commit();
    fwrite(STDOUT, "Importacao concluida.\n");
    fwrite(STDOUT, "Arquivos processados: {$totalArquivos}\n");
    fwrite(STDOUT, "Municipios processados: {$totalMunicipios}\n");
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Falha ao importar territorios: " . $exception->getMessage() . "\n");
    exit(1);
}

function normalizeUf(mixed $value): ?string
{
    $uf = strtoupper(trim((string) $value));
    if (strlen($uf) !== 2) {
        return null;
    }

    return $uf;
}

function normalizeText(mixed $value): ?string
{
    $text = trim((string) $value);
    if ($text === '') {
        return null;
    }

    if (function_exists('mb_check_encoding') && !mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1,Windows-1252,UTF-8');
    }

    return $text;
}

function normalizeHeader(string $column): string
{
    $column = preg_replace('/^\xEF\xBB\xBF/', '', $column) ?? $column;
    return strtoupper(trim($column));
}

function toInt(mixed $value): ?int
{
    $raw = trim((string) $value);
    if ($raw === '' || !is_numeric($raw)) {
        return null;
    }

    return (int) $raw;
}

function toDecimal(mixed $value): ?string
{
    $raw = str_replace(',', '.', trim((string) $value));
    if ($raw === '' || !is_numeric($raw)) {
        return null;
    }

    return (string) $raw;
}
