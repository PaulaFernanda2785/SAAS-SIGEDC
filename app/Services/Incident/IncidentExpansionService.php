<?php

declare(strict_types=1);

namespace App\Services\Incident;

use App\Policies\OperationalPolicy;
use App\Repositories\Operational\IncidentExpansionRepository;
use App\Repositories\Operational\IncidentRepository;
use App\Services\Audit\AuditService;
use App\Services\Institutional\ScopeService;
use App\Support\Request;
use Throwable;

final class IncidentExpansionService
{
    public function __construct(
        private readonly ?IncidentExpansionRepository $expansionRepository = null,
        private readonly ?IncidentRepository $incidentRepository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?OperationalPolicy $operationalPolicy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function workspaceData(array $auth): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);
        $incidentRepo = $this->incidentRepository ?? new IncidentRepository();

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'summary' => ['pai' => 0, 'operacoes' => 0, 'planejamento' => 0, 'seguranca' => 0, 'desmobilizacao' => 0],
                'incident_options' => [],
                'period_options' => [],
                'recent_pai' => [],
                'recent_operations' => [],
                'recent_planning' => [],
                'recent_safety' => [],
                'recent_demobilization' => [],
            ];
        }

        $expansionRepo = $this->expansionRepository ?? new IncidentExpansionRepository();

        return [
            'scope' => $scope,
            'summary' => $expansionRepo->summary($scope),
            'incident_options' => $incidentRepo->incidentOptions($scope),
            'period_options' => $incidentRepo->periodOptions($scope),
            'recent_pai' => $expansionRepo->recentPai($scope),
            'recent_operations' => $expansionRepo->recentOperations($scope),
            'recent_planning' => $expansionRepo->recentPlanning($scope),
            'recent_safety' => $expansionRepo->recentSafety($scope),
            'recent_demobilization' => $expansionRepo->recentDemobilization($scope),
        ];
    }

    public function createPai(array $auth, array $input, Request $request): array
    {
        return $this->createIncidentChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'INCIDENTE_PAI_CREATE',
            entityType: 'incidentes_estrategias_pai',
            requiredField: 'versao_pai',
            secondaryRequiredField: 'estrategia_geral',
            handler: function (array $incident, ?int $periodId, array $input, array $auth): int {
                return ($this->expansionRepository ?? new IncidentExpansionRepository())->createPai([
                    'incidente_id' => (int) $incident['id'],
                    'periodo_operacional_id' => $periodId,
                    'conta_id' => (int) $incident['conta_id'],
                    'orgao_id' => (int) $incident['orgao_id'],
                    'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                    'versao_pai' => (string) $this->requiredText($input['versao_pai']),
                    'estrategia_geral' => (string) $this->requiredText($input['estrategia_geral']),
                    'taticas_prioritarias' => $this->nullableText($input['taticas_prioritarias'] ?? null),
                    'atividades_planejadas' => $this->nullableText($input['atividades_planejadas'] ?? null),
                    'responsavel_execucao' => $this->nullableText($input['responsavel_execucao'] ?? null),
                    'recursos_necessarios' => $this->nullableText($input['recursos_necessarios'] ?? null),
                    'areas_prioritarias' => $this->nullableText($input['areas_prioritarias'] ?? null),
                    'status_pai' => $this->sanitizeEnum(
                        (string) ($input['status_pai'] ?? 'PROPOSTO'),
                        ['PROPOSTO', 'APROVADO', 'EM_EXECUCAO', 'ENCERRADO'],
                        'PROPOSTO'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'PAI registrado com sucesso.',
            failureMessage: 'Falha ao registrar PAI.'
        );
    }

    public function createOperation(array $auth, array $input, Request $request): array
    {
        return $this->createIncidentChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'INCIDENTE_OPERACAO_CREATE',
            entityType: 'incidentes_operacoes_campo',
            requiredField: 'frente_operacional',
            handler: function (array $incident, ?int $periodId, array $input, array $auth): int {
                return ($this->expansionRepository ?? new IncidentExpansionRepository())->createOperation([
                    'incidente_id' => (int) $incident['id'],
                    'periodo_operacional_id' => $periodId,
                    'conta_id' => (int) $incident['conta_id'],
                    'orgao_id' => (int) $incident['orgao_id'],
                    'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                    'frente_operacional' => (string) $this->requiredText($input['frente_operacional']),
                    'setor_operacional' => $this->nullableText($input['setor_operacional'] ?? null),
                    'supervisor_frente' => $this->nullableText($input['supervisor_frente'] ?? null),
                    'missao_tatica' => $this->nullableText($input['missao_tatica'] ?? null),
                    'recursos_designados' => $this->nullableText($input['recursos_designados'] ?? null),
                    'situacao_atual' => $this->nullableText($input['situacao_atual'] ?? null),
                    'resultados_parciais' => $this->nullableText($input['resultados_parciais'] ?? null),
                    'status_operacao' => $this->sanitizeEnum(
                        (string) ($input['status_operacao'] ?? 'ATIVA'),
                        ['ATIVA', 'PAUSADA', 'ENCERRADA'],
                        'ATIVA'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Operacao de campo registrada com sucesso.',
            failureMessage: 'Falha ao registrar operacao de campo.'
        );
    }

    public function createPlanning(array $auth, array $input, Request $request): array
    {
        return $this->createIncidentChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'INCIDENTE_PLANEJAMENTO_CREATE',
            entityType: 'incidentes_planejamento_situacao',
            requiredField: 'situacao_consolidada',
            handler: function (array $incident, ?int $periodId, array $input, array $auth): int {
                return ($this->expansionRepository ?? new IncidentExpansionRepository())->createPlanning([
                    'incidente_id' => (int) $incident['id'],
                    'periodo_operacional_id' => $periodId,
                    'conta_id' => (int) $incident['conta_id'],
                    'orgao_id' => (int) $incident['orgao_id'],
                    'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                    'situacao_consolidada' => (string) $this->requiredText($input['situacao_consolidada']),
                    'prognostico' => $this->nullableText($input['prognostico'] ?? null),
                    'cenario_provavel' => $this->nullableText($input['cenario_provavel'] ?? null),
                    'pendencias_criticas' => $this->nullableText($input['pendencias_criticas'] ?? null),
                    'escalonamento_recomendado' => $this->nullableText($input['escalonamento_recomendado'] ?? null),
                    'status_planejamento' => $this->sanitizeEnum(
                        (string) ($input['status_planejamento'] ?? 'EM_ANALISE'),
                        ['EM_ANALISE', 'VALIDADO', 'ARQUIVADO'],
                        'EM_ANALISE'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Planejamento situacional registrado com sucesso.',
            failureMessage: 'Falha ao registrar planejamento situacional.'
        );
    }

    public function createSafety(array $auth, array $input, Request $request): array
    {
        return $this->createIncidentChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'INCIDENTE_SEGURANCA_CREATE',
            entityType: 'incidentes_seguranca',
            requiredField: 'riscos_operacionais',
            handler: function (array $incident, ?int $periodId, array $input, array $auth): int {
                return ($this->expansionRepository ?? new IncidentExpansionRepository())->createSafety([
                    'incidente_id' => (int) $incident['id'],
                    'periodo_operacional_id' => $periodId,
                    'conta_id' => (int) $incident['conta_id'],
                    'orgao_id' => (int) $incident['orgao_id'],
                    'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                    'riscos_operacionais' => (string) $this->requiredText($input['riscos_operacionais']),
                    'equipes_expostas' => $this->nullableText($input['equipes_expostas'] ?? null),
                    'medidas_controle' => $this->nullableText($input['medidas_controle'] ?? null),
                    'epis_recomendados' => $this->nullableText($input['epis_recomendados'] ?? null),
                    'restricoes_operacionais' => $this->nullableText($input['restricoes_operacionais'] ?? null),
                    'interdicoes' => $this->nullableText($input['interdicoes'] ?? null),
                    'status_seguranca' => $this->sanitizeEnum(
                        (string) ($input['status_seguranca'] ?? 'ATIVA'),
                        ['ATIVA', 'EM_ALERTA', 'ENCERRADA'],
                        'ATIVA'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Registro de seguranca operacional salvo com sucesso.',
            failureMessage: 'Falha ao registrar seguranca operacional.'
        );
    }

    public function createDemobilization(array $auth, array $input, Request $request): array
    {
        return $this->createIncidentChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'INCIDENTE_DESMOBILIZACAO_CREATE',
            entityType: 'incidentes_desmobilizacao',
            requiredField: 'criterios_desmobilizacao',
            handler: function (array $incident, ?int $periodId, array $input, array $auth): int {
                return ($this->expansionRepository ?? new IncidentExpansionRepository())->createDemobilization([
                    'incidente_id' => (int) $incident['id'],
                    'conta_id' => (int) $incident['conta_id'],
                    'orgao_id' => (int) $incident['orgao_id'],
                    'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                    'criterios_desmobilizacao' => (string) $this->requiredText($input['criterios_desmobilizacao']),
                    'recursos_liberados' => $this->nullableText($input['recursos_liberados'] ?? null),
                    'pendencias_finais' => $this->nullableText($input['pendencias_finais'] ?? null),
                    'licoes_iniciais' => $this->nullableText($input['licoes_iniciais'] ?? null),
                    'situacao_final' => $this->nullableText($input['situacao_final'] ?? null),
                    'data_hora_inicio' => $this->parseDateTimeInput($input['data_hora_inicio'] ?? null),
                    'data_hora_encerramento' => $this->parseDateTimeInput($input['data_hora_encerramento'] ?? null),
                    'status_desmobilizacao' => $this->sanitizeEnum(
                        (string) ($input['status_desmobilizacao'] ?? 'PLANEJADA'),
                        ['PLANEJADA', 'EM_ANDAMENTO', 'CONCLUIDA'],
                        'PLANEJADA'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Desmobilizacao registrada com sucesso.',
            failureMessage: 'Falha ao registrar desmobilizacao.'
        );
    }

    private function createIncidentChild(
        array $auth,
        array $input,
        Request $request,
        string $action,
        string $entityType,
        string $requiredField,
        callable $handler,
        string $successMessage,
        string $failureMessage,
        ?string $secondaryRequiredField = null
    ): array {
        if (!$this->canManageExpansion($auth)) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode operar este bloco de gerenciamento de desastre.'];
        }

        $incidentId = (int) ($input['incidente_id'] ?? 0);
        if ($incidentId < 1) {
            return ['ok' => false, 'message' => 'Informe um incidente valido.'];
        }

        if ($this->requiredText($input[$requiredField] ?? null) === null) {
            return ['ok' => false, 'message' => 'Preencha os campos obrigatorios do bloco informado.'];
        }
        if ($secondaryRequiredField !== null && $this->requiredText($input[$secondaryRequiredField] ?? null) === null) {
            return ['ok' => false, 'message' => 'Preencha os campos obrigatorios do bloco informado.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $incidentRepository = $this->incidentRepository ?? new IncidentRepository();
        $incident = $incidentRepository->findIncidentById($scope, $incidentId);
        if ($incident === null) {
            return ['ok' => false, 'message' => 'Incidente nao encontrado no seu escopo de acesso.'];
        }
        if ((string) ($incident['status_incidente'] ?? '') === 'ENCERRADO') {
            return ['ok' => false, 'message' => 'Incidente encerrado nao aceita novos registros deste bloco.'];
        }

        $periodId = $this->nullableInt($input['periodo_operacional_id'] ?? null);
        if ($periodId !== null && !$incidentRepository->periodBelongsToIncident($scope, $periodId, (int) $incident['id'])) {
            return ['ok' => false, 'message' => 'Periodo operacional informado nao pertence ao incidente/escopo atual.'];
        }

        try {
            $id = $handler($incident, $periodId, $input, $auth);
            $this->audit($auth, $request, $action, $entityType, $id, [
                'incidente_id' => (int) $incident['id'],
                'periodo_operacional_id' => $periodId,
            ]);
            return ['ok' => true, 'message' => $successMessage];
        } catch (Throwable) {
            return ['ok' => false, 'message' => $failureMessage];
        }
    }

    private function canManageExpansion(array $auth): bool
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        return ($this->operationalPolicy ?? new OperationalPolicy())->canManageDisasterExpansion($profiles);
    }

    private function audit(
        array $auth,
        Request $request,
        string $action,
        string $entityType,
        ?int $entityId,
        array $details
    ): void {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'modulo_codigo' => 'DISASTER_EXPANSION',
            'acao' => $action,
            'resultado' => 'SUCESSO',
            'entidade_tipo' => $entityType,
            'entidade_id' => $entityId,
            'detalhes' => $details,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function requiredText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function nullableInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private function parseDateTimeInput(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = str_replace('T', ' ', $raw);
        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
