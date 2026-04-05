<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Domain\Enum\UserProfile;
use App\Repositories\Audit\AccessLogRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\Audit\AuditService;
use App\Support\Request;

final class AuthService
{
    public function __construct(
        private readonly ?UserRepository $userRepository = null,
        private readonly ?SessionService $sessionService = null,
        private readonly ?ContractAccessService $contractAccessService = null,
        private readonly ?AuditService $auditService = null,
        private readonly ?AccessLogRepository $accessLogRepository = null
    ) {
    }

    public function attempt(string $login, string $password, Request $request): array
    {
        $userRepository = $this->userRepository ?? new UserRepository();
        $sessionService = $this->sessionService ?? new SessionService();
        $contractAccessService = $this->contractAccessService ?? new ContractAccessService();
        $auditService = $this->auditService ?? new AuditService();
        $accessLogRepository = $this->accessLogRepository ?? new AccessLogRepository();

        $user = $userRepository->findByLogin($login);
        if ($user === null) {
            $accessLogRepository->record([
                'evento' => 'LOGIN_FAILED',
                'resultado' => 'FALHA',
                'motivo' => 'usuario_nao_encontrado',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Credenciais invalidas.'];
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_FAILED',
                'resultado' => 'FALHA',
                'motivo' => 'senha_invalida',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Credenciais invalidas.'];
        }

        if ((string) $user['status_usuario'] !== 'ATIVO') {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_BLOCKED',
                'resultado' => 'FALHA',
                'motivo' => 'usuario_inativo_ou_bloqueado',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Usuario inativo ou bloqueado.'];
        }

        if ((string) ($user['status_conta'] ?? '') !== 'ATIVA') {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_BLOCKED',
                'resultado' => 'FALHA',
                'motivo' => 'conta_inativa_ou_bloqueada',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Conta contratante inativa ou bloqueada.'];
        }

        if ((string) ($user['status_orgao'] ?? '') !== 'ATIVO') {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_BLOCKED',
                'resultado' => 'FALHA',
                'motivo' => 'orgao_inativo_ou_bloqueado',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Orgao inativo ou bloqueado.'];
        }

        $contractCheck = $contractAccessService->evaluate((int) $user['conta_id']);
        if (!($contractCheck['ok'] ?? false)) {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_BLOCKED',
                'resultado' => 'FALHA',
                'motivo' => (string) ($contractCheck['reason'] ?? 'contrato_invalido'),
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => (string) ($contractCheck['message'] ?? 'Acesso indisponivel para a assinatura atual.')];
        }

        $profiles = $userRepository->profileCodes((int) $user['id']);
        if ($profiles === []) {
            return ['ok' => false, 'message' => 'Usuario sem perfil vinculado.'];
        }

        $isAdminMaster = in_array(UserProfile::ADMIN_MASTER, $profiles, true);
        $userUf = strtoupper(trim((string) ($user['uf_sigla'] ?? '')));
        $contractUf = strtoupper(trim((string) ($contractCheck['assinatura']['uf_sigla'] ?? '')));
        if (
            !$isAdminMaster
            && $userUf !== ''
            && $contractUf !== ''
            && $userUf !== $contractUf
        ) {
            $accessLogRepository->record([
                'usuario_id' => (int) $user['id'],
                'conta_id' => (int) $user['conta_id'],
                'orgao_id' => (int) $user['orgao_id'],
                'evento' => 'LOGIN_BLOCKED',
                'resultado' => 'FALHA',
                'motivo' => 'uf_usuario_divergente_da_assinatura',
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => false, 'message' => 'Usuario fora do UF de origem da assinatura contratada.'];
        }

        $scopes = $userRepository->scopeCodes((int) $user['id']);

        $session = $sessionService->open(
            $user,
            $profiles,
            $request,
            [
                'assinatura_id' => isset($contractCheck['assinatura']['id']) ? (int) $contractCheck['assinatura']['id'] : null,
                'status_assinatura' => $contractCheck['assinatura']['status_assinatura'] ?? null,
                'modulos_liberados' => $contractCheck['modulos_liberados'] ?? [],
                'escopos' => $scopes,
                'is_demo_trial' => $this->isDemoTrial($contractCheck['assinatura'] ?? []),
            ]
        );

        $accessLogRepository->record([
            'usuario_id' => (int) $user['id'],
            'conta_id' => (int) $user['conta_id'],
            'orgao_id' => (int) $user['orgao_id'],
            'evento' => 'LOGIN_SUCCESS',
            'resultado' => 'SUCESSO',
            'motivo' => null,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);

        $auditService->log([
            'conta_id' => (int) $user['conta_id'],
            'orgao_id' => (int) $user['orgao_id'],
            'unidade_id' => isset($user['unidade_id']) ? (int) $user['unidade_id'] : null,
            'usuario_id' => (int) $user['id'],
            'modulo_codigo' => 'AUTH',
            'acao' => 'AUTH_LOGIN',
            'resultado' => 'SUCESSO',
            'entidade_tipo' => 'usuarios',
            'entidade_id' => (int) $user['id'],
            'detalhes' => ['perfis' => $profiles],
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);

        return [
            'ok' => true,
            'redirect' => $session['area'] === 'admin' ? '/admin' : '/operational',
        ];
    }

    public function logout(Request $request): void
    {
        $accessLogRepository = $this->accessLogRepository ?? new AccessLogRepository();
        $auditService = $this->auditService ?? new AuditService();
        $sessionService = $this->sessionService ?? new SessionService();

        $auth = $_SESSION['auth'] ?? null;
        if (is_array($auth)) {
            $accessLogRepository->record([
                'usuario_id' => (int) ($auth['usuario_id'] ?? 0),
                'conta_id' => (int) ($auth['conta_id'] ?? 0),
                'orgao_id' => (int) ($auth['orgao_id'] ?? 0),
                'evento' => 'LOGOUT',
                'resultado' => 'SUCESSO',
                'motivo' => null,
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            $auditService->log([
                'conta_id' => $auth['conta_id'] ?? null,
                'orgao_id' => $auth['orgao_id'] ?? null,
                'unidade_id' => $auth['unidade_id'] ?? null,
                'usuario_id' => $auth['usuario_id'] ?? null,
                'modulo_codigo' => 'AUTH',
                'acao' => 'AUTH_LOGOUT',
                'resultado' => 'SUCESSO',
                'entidade_tipo' => 'usuarios',
                'entidade_id' => $auth['usuario_id'] ?? null,
                'detalhes' => [],
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        $sessionService->close();
    }

    public function user(): ?array
    {
        $auth = $_SESSION['auth'] ?? null;

        return is_array($auth) ? $auth : null;
    }

    private function isDemoTrial(array $contract): bool
    {
        $status = strtoupper((string) ($contract['status_assinatura'] ?? ''));
        $reason = strtoupper((string) ($contract['motivo_status'] ?? ''));

        return $status === 'TRIAL' && str_contains($reason, 'TRIAL_DEMO_PUBLICO_3_DIAS');
    }
}
