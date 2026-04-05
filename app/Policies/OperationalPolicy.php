<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Enum\UserProfile;

final class OperationalPolicy
{
    public function canAccessModule(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
            UserProfile::LEITOR,
        ]);
    }

    public function canOpenIncident(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
        ]);
    }

    public function canRegisterBriefing(array $profiles): bool
    {
        return $this->canOpenIncident($profiles);
    }

    public function canManageCommand(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
        ]);
    }

    public function canCreatePeriod(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
        ]);
    }

    public function canCreateRecord(array $profiles): bool
    {
        return $this->canOpenIncident($profiles);
    }

    public function canAccessPlancon(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
            UserProfile::LEITOR,
        ]);
    }

    public function canManagePlancon(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
        ]);
    }

    public function canAccessDisasterExpansion(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
        ]);
    }

    public function canManageDisasterExpansion(array $profiles): bool
    {
        return $this->canAccessDisasterExpansion($profiles);
    }

    public function canAccessIntelligence(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
            UserProfile::LEITOR,
        ]);
    }

    public function canAccessDocuments(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
            UserProfile::LEITOR,
        ]);
    }

    public function canUploadDocuments(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
        ]);
    }

    public function canAccessGovernance(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
        ]);
    }

    public function canAcceptGovernanceTerm(array $profiles): bool
    {
        return $this->canAccessGovernance($profiles);
    }

    public function canAccessAdvancedReports(array $profiles): bool
    {
        return $this->hasAnyProfile($profiles, [
            UserProfile::GESTOR,
            UserProfile::COORDENADOR,
            UserProfile::ANALISTA,
            UserProfile::OPERADOR,
            UserProfile::LEITOR,
        ]);
    }

    private function hasAnyProfile(array $profiles, array $allowed): bool
    {
        foreach ($profiles as $profile) {
            if (in_array((string) $profile, $allowed, true)) {
                return true;
            }
        }

        return false;
    }
}
