<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Domain\Enum\UserProfile;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\Auth\UserRepository;
use App\Support\Request;
use DateTimeImmutable;

final class SessionService
{
    public function __construct(
        private readonly ?SessionRepository $sessionRepository = null,
        private readonly ?UserRepository $userRepository = null
    ) {
    }

    public function open(array $user, array $profiles, Request $request, array $contractContext = []): array
    {
        session_regenerate_id(true);

        $sessionRepository = $this->sessionRepository ?? new SessionRepository();
        $userRepository = $this->userRepository ?? new UserRepository();

        $expiration = (new DateTimeImmutable())
            ->modify('+' . (int) config('session.lifetime', 120) . ' minutes')
            ->format('Y-m-d H:i:s');

        $primaryProfile = $profiles[0] ?? UserProfile::CONVIDADO;
        $area = $this->resolveAreaByProfiles($profiles);

        $sessionRecordId = $sessionRepository->create([
            'usuario_id' => (int) $user['id'],
            'session_id_hash' => hash('sha256', session_id()),
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
            'expira_em' => $expiration,
            'status_sessao' => 'ATIVA',
        ]);

        $userRepository->touchLastAccess((int) $user['id']);

        $_SESSION['auth'] = [
            'usuario_id' => (int) $user['id'],
            'conta_id' => isset($user['conta_id']) ? (int) $user['conta_id'] : null,
            'orgao_id' => isset($user['orgao_id']) ? (int) $user['orgao_id'] : null,
            'unidade_id' => isset($user['unidade_id']) ? (int) $user['unidade_id'] : null,
            'nome_completo' => (string) $user['nome_completo'],
            'email_login' => (string) $user['email_login'],
            'perfis' => $profiles,
            'perfil_primario' => $primaryProfile,
            'area' => $area,
            'assinatura_id' => $contractContext['assinatura_id'] ?? null,
            'status_assinatura' => $contractContext['status_assinatura'] ?? null,
            'modulos_liberados' => $contractContext['modulos_liberados'] ?? [],
            'sessao_usuario_id' => $sessionRecordId,
            'ultimo_toque' => time(),
        ];

        return $_SESSION['auth'];
    }

    public function touch(): void
    {
        if (!isset($_SESSION['auth'])) {
            return;
        }

        $lastTouch = (int) ($_SESSION['auth']['ultimo_toque'] ?? 0);
        if ((time() - $lastTouch) < 60) {
            return;
        }

        $sessionRecordId = (int) ($_SESSION['auth']['sessao_usuario_id'] ?? 0);
        if ($sessionRecordId > 0) {
            ($this->sessionRepository ?? new SessionRepository())->touch($sessionRecordId);
        }

        $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);
        if ($userId > 0) {
            ($this->userRepository ?? new UserRepository())->touchLastAccess($userId);
        }

        $_SESSION['auth']['ultimo_toque'] = time();
    }

    public function close(): void
    {
        $sessionRecordId = (int) ($_SESSION['auth']['sessao_usuario_id'] ?? 0);
        if ($sessionRecordId > 0) {
            ($this->sessionRepository ?? new SessionRepository())->close($sessionRecordId, 'ENCERRADA');
        }

        unset($_SESSION['auth']);
        session_regenerate_id(true);
    }

    private function resolveAreaByProfiles(array $profiles): string
    {
        foreach ($profiles as $profile) {
            if (in_array($profile, UserProfile::adminProfiles(), true)) {
                return 'admin';
            }
        }

        return 'operational';
    }
}
