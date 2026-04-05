<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Enum\UserProfile;

final class AdminEnterprisePolicy
{
    public function canAccess(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
            UserProfile::FINANCEIRO,
        ]);
    }

    public function canManageFeatures(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
        ]);
    }

    public function canManageApi(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
        ]);
    }

    public function canManageIntegrations(array $profiles): bool
    {
        return $this->canManageApi($profiles);
    }

    public function canManageAutomations(array $profiles): bool
    {
        return $this->canManageApi($profiles);
    }

    public function canManageSla(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
            UserProfile::FINANCEIRO,
        ]);
    }

    public function canManageSupport(array $profiles): bool
    {
        return $this->canManageSla($profiles);
    }

    public function canRegisterDigitalSignature(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
        ]);
    }

    public function canViewAnalytics(array $profiles): bool
    {
        return $this->hasAny($profiles, [
            UserProfile::ADMIN_MASTER,
            UserProfile::ADMIN_ORGAO,
            UserProfile::SUPORTE,
            UserProfile::FINANCEIRO,
        ]);
    }

    private function hasAny(array $profiles, array $allowed): bool
    {
        foreach ($profiles as $profile) {
            if (in_array((string) $profile, $allowed, true)) {
                return true;
            }
        }

        return false;
    }
}
