<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Policies\OperationalPolicy;
use App\Repositories\Audit\GovernanceRepository;
use App\Services\Institutional\ScopeService;
use App\Support\Request;

final class GovernanceService
{
    public function __construct(
        private readonly ?GovernanceRepository $repository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?OperationalPolicy $policy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function workspaceData(array $auth, array $filters): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);
        $dateFrom = $this->sanitizeDate($filters['data_inicio'] ?? null);
        $dateTo = $this->sanitizeDate($filters['data_fim'] ?? null);
        $resultado = $this->sanitizeEnum((string) ($filters['resultado'] ?? ''), ['SUCESSO', 'FALHA', 'NEGADO'], '');
        $resultado = $resultado === '' ? null : $resultado;
        $modulo = $this->sanitizeText($filters['modulo_codigo'] ?? null);
        $filtersNormalized = [
            'data_inicio' => $dateFrom,
            'data_fim' => $dateTo,
            'resultado' => $resultado,
            'modulo_codigo' => $modulo,
        ];

        $termCode = (string) config('governance.current_term_code', 'OPER_GOV_BASE');
        $termVersion = (string) config('governance.current_term_version', '2026.04');

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'filters' => $filtersNormalized,
                'summary' => ['total_eventos' => 0, 'total_sucesso' => 0, 'total_falha' => 0, 'total_negado' => 0],
                'action_frequency' => [],
                'recent_logs' => [],
                'recent_term_acceptances' => [],
                'term' => [
                    'code' => $termCode,
                    'version' => $termVersion,
                    'title' => (string) config('governance.current_term_title', 'Termo de Governanca Operacional'),
                ],
                'term_accepted' => false,
            ];
        }

        $repository = $this->repository ?? new GovernanceRepository();
        $termAccepted = $repository->hasTermAcceptance(
            (int) ($auth['usuario_id'] ?? 0),
            $termCode,
            $termVersion
        );

        return [
            'scope' => $scope,
            'filters' => $filtersNormalized,
            'summary' => $repository->summary($scope, $dateFrom, $dateTo),
            'action_frequency' => $repository->actionFrequency($scope, $dateFrom, $dateTo, 20),
            'recent_logs' => $repository->recentLogs($scope, $resultado, $modulo, 120),
            'recent_term_acceptances' => $repository->recentTermAcceptances($scope, 40),
            'term' => [
                'code' => $termCode,
                'version' => $termVersion,
                'title' => (string) config('governance.current_term_title', 'Termo de Governanca Operacional'),
                'description' => (string) config('governance.current_term_description', 'Compromisso com rastreabilidade, segregacao de escopo e conduta segura.'),
            ],
            'term_accepted' => $termAccepted,
        ];
    }

    public function acceptCurrentTerm(array $auth, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->policy ?? new OperationalPolicy())->canAcceptGovernanceTerm($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode registrar aceite de termo de governanca.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para aceite do termo.'];
        }

        $termCode = (string) config('governance.current_term_code', 'OPER_GOV_BASE');
        $termVersion = (string) config('governance.current_term_version', '2026.04');
        $repository = $this->repository ?? new GovernanceRepository();

        $alreadyAccepted = $repository->hasTermAcceptance((int) $auth['usuario_id'], $termCode, $termVersion);
        if ($alreadyAccepted) {
            return ['ok' => true, 'message' => 'Termo atual ja estava aceito para este usuario.'];
        }

        $repository->registerTermAcceptance([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'termo_codigo' => $termCode,
            'versao_termo' => $termVersion,
            'origem_ip' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
            'detalhes' => [
                'origem' => 'painel_governanca_operacional',
            ],
        ]);

        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'modulo_codigo' => 'GOVERNANCE',
            'acao' => 'GOVERNANCE_TERM_ACCEPT',
            'resultado' => 'SUCESSO',
            'entidade_tipo' => 'governanca_termos_aceite',
            'entidade_id' => null,
            'detalhes' => [
                'termo_codigo' => $termCode,
                'versao_termo' => $termVersion,
            ],
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);

        return ['ok' => true, 'message' => 'Aceite de termo registrado com sucesso.'];
    }

    private function sanitizeDate(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1 ? $raw : null;
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function sanitizeText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }
}
