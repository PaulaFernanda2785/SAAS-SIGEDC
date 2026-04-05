<?php

declare(strict_types=1);

namespace App\Services\Territory;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Repositories\Territory\TerritoryRepository;

final class TerritoryLookupService
{
    public function __construct(private readonly ?TerritoryRepository $territoryRepository = null)
    {
    }

    public function accessibleUfs(array $auth): array
    {
        $all = $this->readCache('ufs:all');
        if (!is_array($all)) {
            $all = ($this->territoryRepository ?? new TerritoryRepository())->ufs();
            $this->writeCache('ufs:all', $all);
        }

        if ($this->isAdminMaster($auth)) {
            return $all;
        }

        $userUf = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        if ($userUf === null) {
            return [];
        }

        return array_values(array_filter(
            $all,
            static fn(array $item): bool => strtoupper((string) ($item['sigla'] ?? '')) === $userUf
        ));
    }

    public function municipiosByUf(array $auth, string $ufSigla, string $query, int $limit = 25): array
    {
        $ufSigla = BrazilUf::normalize($ufSigla);
        if ($ufSigla === null) {
            return [];
        }

        if (!$this->canReadUf($auth, $ufSigla)) {
            return [];
        }

        $normalizedQuery = $this->normalizeQuery($query);
        $minChars = (int) config('territory.autocomplete_min_chars', 2);
        if ($minChars < 1) {
            $minChars = 2;
        }

        if ($normalizedQuery === '' || mb_strlen($normalizedQuery) < $minChars) {
            return [];
        }

        $prefix = $this->cachePrefix($normalizedQuery);
        $key = 'municipios:' . $ufSigla . ':' . $prefix;
        $cached = $this->readCache($key);
        if (is_array($cached)) {
            return $this->filterByQuery($cached, $normalizedQuery, $limit);
        }

        $sourceLimit = (int) config('territory.autocomplete_source_limit', 180);
        if ($sourceLimit < $limit) {
            $sourceLimit = $limit;
        }

        $rows = ($this->territoryRepository ?? new TerritoryRepository())
            ->municipiosByUf($ufSigla, $prefix, $sourceLimit);
        $this->writeCache($key, $rows);

        return $this->filterByQuery($rows, $normalizedQuery, $limit);
    }

    public function canReadUf(array $auth, string $targetUf): bool
    {
        if ($this->isAdminMaster($auth)) {
            return true;
        }

        $userUf = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        if ($userUf === null) {
            return false;
        }

        return $userUf === strtoupper($targetUf);
    }

    public function normalizeQuery(string $query): string
    {
        $query = preg_replace('/\s+/', ' ', trim($query)) ?? '';
        $maxChars = (int) config('territory.autocomplete_max_chars', 80);
        if ($maxChars < 1) {
            $maxChars = 80;
        }

        return mb_substr($query, 0, $maxChars);
    }

    private function cachePrefix(string $query): string
    {
        $prefixSize = (int) config('territory.autocomplete_cache_prefix_chars', 3);
        if ($prefixSize < 1) {
            $prefixSize = 3;
        }

        return mb_strtoupper(mb_substr($query, 0, $prefixSize));
    }

    private function filterByQuery(array $rows, string $query, int $limit): array
    {
        $queryUpper = $this->normalizeForCompare($query);
        $filtered = [];
        foreach ($rows as $row) {
            $name = $this->normalizeForCompare((string) ($row['nome_municipio'] ?? ''));
            if ($name === '' || !str_starts_with($name, $queryUpper)) {
                continue;
            }

            $filtered[] = $row;
            if (count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }

    private function normalizeForCompare(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }

        $value = preg_replace('/[^A-Za-z0-9 ]/', '', $value) ?? $value;
        return strtoupper($value);
    }

    private function isAdminMaster(array $auth): bool
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        return in_array(UserProfile::ADMIN_MASTER, $profiles, true);
    }

    private function readCache(string $key): ?array
    {
        $file = $this->cacheFilePath($key);
        if (!is_file($file)) {
            return null;
        }

        $ttl = (int) config('territory.cache_ttl_seconds', 600);
        if ($ttl < 1) {
            $ttl = 600;
        }

        $raw = file_get_contents($file);
        if ($raw === false || $raw === '') {
            return null;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return null;
        }

        $createdAt = (int) ($payload['created_at'] ?? 0);
        if ($createdAt < 1 || (time() - $createdAt) > $ttl) {
            @unlink($file);
            return null;
        }

        $data = $payload['data'] ?? null;
        return is_array($data) ? $data : null;
    }

    private function writeCache(string $key, array $data): void
    {
        $file = $this->cacheFilePath($key);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $payload = json_encode([
            'created_at' => time(),
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            return;
        }

        $tmp = $file . '.tmp';
        if (@file_put_contents($tmp, $payload, LOCK_EX) === false) {
            return;
        }

        @rename($tmp, $file);
    }

    private function cacheFilePath(string $key): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-:]/', '', $key) ?? $key;
        $prefix = str_replace(':', '_', mb_substr($safe, 0, 40));
        return storage_path('cache/territory/' . $prefix . '_' . sha1($key) . '.json');
    }
}
