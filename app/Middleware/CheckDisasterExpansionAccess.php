<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Policies\OperationalPolicy;
use App\Services\Audit\AuditService;
use App\Support\Request;
use App\Support\Response;

final class CheckDisasterExpansionAccess implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        $modules = is_array($auth['modulos_liberados'] ?? null) ? $auth['modulos_liberados'] : [];

        if (!in_array('DISASTER_EXPANSION', $modules, true)) {
            $this->auditDenied($request, $auth, 'modulo_disaster_expansion_nao_liberado');
            return Response::view('errors/403', [
                'title' => 'Acesso negado',
                'message' => 'Modulo de expansao de desastres nao esta liberado para a assinatura atual.',
            ], 'public', 403);
        }

        if (!(new OperationalPolicy())->canAccessDisasterExpansion($profiles)) {
            $this->auditDenied($request, $auth, 'perfil_sem_acesso_disaster_expansion');
            return Response::view('errors/403', [
                'title' => 'Acesso negado',
                'message' => 'Seu perfil nao possui permissao para acessar este modulo.',
            ], 'public', 403);
        }

        return $next($request);
    }

    private function auditDenied(Request $request, array $auth, string $reason): void
    {
        (new AuditService())->log([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'modulo_codigo' => 'DISASTER_EXPANSION',
            'acao' => 'DISASTER_EXPANSION_ACCESS_DENIED',
            'resultado' => 'NEGADO',
            'entidade_tipo' => 'acesso_disaster_expansion',
            'entidade_id' => null,
            'detalhes' => [
                'reason' => $reason,
                'uri' => $request->uri(),
            ],
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
