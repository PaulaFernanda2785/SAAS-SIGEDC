<?php

declare(strict_types=1);

namespace App\Services\Institutional;

use App\Domain\Enum\InstitutionScope;

final class ScopeService
{
    public function resolveActiveScope(array $auth): string
    {
        $scopes = $this->sanitizeScopes($auth['escopos'] ?? []);
        if ($scopes === []) {
            return InstitutionScope::PROPRIO_ORGAO;
        }

        foreach (InstitutionScope::restrictivePriority() as $scope) {
            if (in_array($scope, $scopes, true)) {
                return $scope;
            }
        }

        return InstitutionScope::PROPRIO_ORGAO;
    }

    public function scopeFilter(array $auth): array
    {
        $scope = $this->resolveActiveScope($auth);
        $contaId = (int) ($auth['conta_id'] ?? 0);
        $orgaoId = (int) ($auth['orgao_id'] ?? 0);
        $unidadeId = (int) ($auth['unidade_id'] ?? 0);

        return [
            'escopo_ativo' => $scope,
            'conta_id' => $contaId > 0 ? $contaId : null,
            'orgao_id' => $orgaoId > 0 ? $orgaoId : null,
            'unidade_id' => $unidadeId > 0 ? $unidadeId : null,
            'restrict_to_orgao' => in_array($scope, [
                InstitutionScope::PROPRIA_UNIDADE,
                InstitutionScope::PROPRIO_ORGAO,
                InstitutionScope::MUNICIPAL,
                InstitutionScope::REGIONAL,
                InstitutionScope::ESTADUAL,
            ], true),
            'restrict_to_unidade' => $scope === InstitutionScope::PROPRIA_UNIDADE,
        ];
    }

    public function hasValidContext(array $auth): bool
    {
        $filter = $this->scopeFilter($auth);
        if (($filter['conta_id'] ?? null) === null) {
            return false;
        }

        if (($filter['restrict_to_orgao'] ?? false) && ($filter['orgao_id'] ?? null) === null) {
            return false;
        }

        if (($filter['restrict_to_unidade'] ?? false) && ($filter['unidade_id'] ?? null) === null) {
            return false;
        }

        return true;
    }

    public function resolveTargetUnitId(array $auth, ?int $requestedUnitId): ?int
    {
        $requestedUnitId = ($requestedUnitId ?? 0) > 0 ? $requestedUnitId : null;
        $filter = $this->scopeFilter($auth);

        if (($filter['restrict_to_unidade'] ?? false) === true) {
            return $filter['unidade_id'] ?? null;
        }

        return $requestedUnitId ?? ($filter['unidade_id'] ?? null);
    }

    public function canWriteToUnit(array $auth, ?int $targetUnitId): bool
    {
        $filter = $this->scopeFilter($auth);
        if (($filter['restrict_to_unidade'] ?? false) === false) {
            return true;
        }

        return ($filter['unidade_id'] ?? null) !== null
            && $targetUnitId !== null
            && (int) $filter['unidade_id'] === (int) $targetUnitId;
    }

    private function sanitizeScopes(mixed $scopes): array
    {
        if (!is_array($scopes)) {
            return [];
        }

        $allowed = InstitutionScope::all();
        $normalized = [];
        foreach ($scopes as $scope) {
            $scope = strtoupper(trim((string) $scope));
            if ($scope !== '' && in_array($scope, $allowed, true)) {
                $normalized[] = $scope;
            }
        }

        return array_values(array_unique($normalized));
    }
}
