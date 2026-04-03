<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\Auth\PasswordResetRepository;
use App\Repositories\Auth\UserRepository;
use App\Support\Request;
use DateTimeImmutable;
use Throwable;

final class PasswordResetService
{
    public function __construct(
        private readonly ?UserRepository $userRepository = null,
        private readonly ?PasswordResetRepository $passwordResetRepository = null
    ) {
    }

    public function requestToken(string $emailLogin, Request $request): array
    {
        $userRepository = $this->userRepository ?? new UserRepository();
        $passwordResetRepository = $this->passwordResetRepository ?? new PasswordResetRepository();

        if (!$passwordResetRepository->tableExists()) {
            return [
                'ok' => false,
                'message' => 'Recuperacao de senha indisponivel. Execute o schema da Fase 1.',
            ];
        }

        $emailLogin = trim(strtolower($emailLogin));
        $user = $userRepository->findByLogin($emailLogin);

        if ($user === null) {
            return [
                'ok' => true,
                'message' => 'Se o login existir, enviaremos as instrucoes de recuperacao.',
            ];
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

        $passwordResetRepository->invalidateOpenTokensForEmail((string) $user['email_login']);
        $passwordResetRepository->create([
            'usuario_id' => (int) $user['id'],
            'email_login' => (string) $user['email_login'],
            'token_hash' => $tokenHash,
            'expira_em' => $expiresAt,
            'solicitado_ip' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);

        $response = [
            'ok' => true,
            'message' => 'Solicitacao registrada. Use o token para redefinir a senha.',
            'token_expires_at' => $expiresAt,
        ];

        if ((string) config('app.env', 'local') !== 'production') {
            $response['token'] = $token;
        }

        return $response;
    }

    public function resetPassword(string $token, string $newPassword): array
    {
        $passwordResetRepository = $this->passwordResetRepository ?? new PasswordResetRepository();
        $userRepository = $this->userRepository ?? new UserRepository();

        if (!$passwordResetRepository->tableExists()) {
            return [
                'ok' => false,
                'message' => 'Recuperacao de senha indisponivel. Execute o schema da Fase 1.',
            ];
        }

        $tokenHash = hash('sha256', $token);
        $reset = $passwordResetRepository->findOpenByTokenHash($tokenHash);
        if ($reset === null) {
            return ['ok' => false, 'message' => 'Token invalido ou ja utilizado.'];
        }

        if (strtotime((string) $reset['expira_em']) < time()) {
            $passwordResetRepository->consume((int) $reset['id']);
            return ['ok' => false, 'message' => 'Token expirado. Solicite uma nova recuperacao.'];
        }

        try {
            $userRepository->updatePasswordById((int) $reset['usuario_id'], password_hash($newPassword, PASSWORD_DEFAULT));
            $passwordResetRepository->consume((int) $reset['id']);
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao redefinir senha.'];
        }

        return ['ok' => true, 'message' => 'Senha redefinida com sucesso.'];
    }
}
