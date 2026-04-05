<?php

declare(strict_types=1);

namespace App\Services\Plancon;

use App\Domain\Enum\BrazilUf;
use App\Policies\OperationalPolicy;
use App\Repositories\Operational\UnitRepository;
use App\Repositories\Plancon\PlanconRepository;
use App\Services\Audit\AuditService;
use App\Services\Institutional\ScopeService;
use App\Support\Request;
use Throwable;

final class PlanconService
{
    public function __construct(
        private readonly ?PlanconRepository $planconRepository = null,
        private readonly ?UnitRepository $unitRepository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?OperationalPolicy $operationalPolicy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function workspaceData(array $auth): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'summary' => ['total_plancons' => 0, 'plancons_ativos' => 0, 'plancons_em_revisao' => 0, 'plancons_vencidos' => 0],
                'plancons' => [],
                'plancon_options' => [],
                'unit_options' => [],
                'recent_risks' => [],
                'recent_scenarios' => [],
                'recent_resources' => [],
            ];
        }

        $repository = $this->planconRepository ?? new PlanconRepository();
        $unitRepository = $this->unitRepository ?? new UnitRepository();

        return [
            'scope' => $scope,
            'summary' => $repository->summary($scope),
            'plancons' => $repository->plancons($scope),
            'plancon_options' => $repository->planconOptions($scope),
            'unit_options' => $unitRepository->optionsByScope($scope),
            'recent_risks' => $repository->recentRisks($scope),
            'recent_scenarios' => $repository->recentScenarios($scope),
            'recent_resources' => $repository->recentResources($scope),
        ];
    }

    public function createPlancon(array $auth, array $input, Request $request): array
    {
        if (!$this->canManagePlancon($auth)) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode criar PLANCON.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para criar PLANCON.'];
        }

        $title = $this->requiredText($input['titulo_plano'] ?? null);
        if ($title === null) {
            return ['ok' => false, 'message' => 'Informe o titulo do plano.'];
        }

        $statusPlancon = $this->sanitizeEnum(
            (string) ($input['status_plancon'] ?? 'RASCUNHO'),
            ['RASCUNHO', 'ATIVO', 'EM_REVISAO', 'ARQUIVADO', 'VENCIDO'],
            'RASCUNHO'
        );

        $scope = $scopeService->scopeFilter($auth);
        $unitRepository = $this->unitRepository ?? new UnitRepository();
        $requestedUnitId = $this->nullableInt($input['unidade_id'] ?? null);
        if ($requestedUnitId !== null && !$unitRepository->existsInScope($scope, $requestedUnitId)) {
            return ['ok' => false, 'message' => 'Unidade informada nao pertence ao escopo institucional ativo.'];
        }

        $targetUnitId = $scopeService->resolveTargetUnitId($auth, $requestedUnitId);
        if ($targetUnitId !== null && !$unitRepository->existsInScope($scope, $targetUnitId)) {
            return ['ok' => false, 'message' => 'Unidade alvo invalida para o seu escopo institucional.'];
        }

        try {
            $id = ($this->planconRepository ?? new PlanconRepository())->createPlancon([
                'conta_id' => (int) $auth['conta_id'],
                'orgao_id' => (int) $auth['orgao_id'],
                'unidade_id' => $targetUnitId,
                'titulo_plano' => $title,
                'municipio_estado' => $this->normalizeMunicipioValue(
                    $input['municipio_estado'] ?? null,
                    $input['uf_sigla_referencia'] ?? ($auth['uf_sigla'] ?? null)
                ),
                'versao_documento' => $this->nullableText($input['versao_documento'] ?? null),
                'data_elaboracao' => $this->sanitizeDate($input['data_elaboracao'] ?? null),
                'data_ultima_atualizacao' => $this->sanitizeDate($input['data_ultima_atualizacao'] ?? null),
                'responsavel_tecnico' => $this->nullableText($input['responsavel_tecnico'] ?? null),
                'contato_institucional' => $this->nullableText($input['contato_institucional'] ?? null),
                'vigencia_inicio' => $this->sanitizeDate($input['vigencia_inicio'] ?? null),
                'vigencia_fim' => $this->sanitizeDate($input['vigencia_fim'] ?? null),
                'area_abrangencia' => $this->nullableText($input['area_abrangencia'] ?? null),
                'tipo_desastre_principal' => $this->nullableText($input['tipo_desastre_principal'] ?? null),
                'outros_desastres_associados' => $this->nullableText($input['outros_desastres_associados'] ?? null),
                'base_legal_utilizada' => $this->nullableText($input['base_legal_utilizada'] ?? null),
                'objetivo_geral' => $this->nullableText($input['objetivo_geral'] ?? null),
                'objetivos_especificos' => $this->nullableText($input['objetivos_especificos'] ?? null),
                'publico_alvo' => $this->nullableText($input['publico_alvo'] ?? null),
                'status_plancon' => $statusPlancon,
                'observacoes_gerais' => $this->nullableText($input['observacoes_gerais'] ?? null),
                'criado_por_usuario_id' => (int) $auth['usuario_id'],
                'atualizado_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'PLANCON_CREATE', 'plancons', $id, [
                'titulo_plano' => $title,
                'status_plancon' => $statusPlancon,
            ]);

            return ['ok' => true, 'message' => 'PLANCON criado com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao criar PLANCON. Verifique duplicidade de titulo/versao.'];
        }
    }

    public function createRisk(array $auth, array $input, Request $request): array
    {
        return $this->createPlanconChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'PLANCON_RISCO_CREATE',
            entityType: 'plancon_riscos',
            requiredField: 'descricao_risco',
            handler: function (array $plancon, array $input, array $auth): int {
                return ($this->planconRepository ?? new PlanconRepository())->createRisk([
                    'plancon_id' => (int) $plancon['id'],
                    'conta_id' => (int) $plancon['conta_id'],
                    'orgao_id' => (int) $plancon['orgao_id'],
                    'unidade_id' => $plancon['unidade_id'] !== null ? (int) $plancon['unidade_id'] : null,
                    'tipo_ameaca' => $this->nullableText($input['tipo_ameaca'] ?? null),
                    'descricao_risco' => (string) $this->requiredText($input['descricao_risco']),
                    'origem_risco' => $this->nullableText($input['origem_risco'] ?? null),
                    'historico_ocorrencias' => $this->nullableText($input['historico_ocorrencias'] ?? null),
                    'frequencia_ocorrencia' => $this->nullableText($input['frequencia_ocorrencia'] ?? null),
                    'periodo_sazonal' => $this->nullableText($input['periodo_sazonal'] ?? null),
                    'areas_suscetiveis' => $this->nullableText($input['areas_suscetiveis'] ?? null),
                    'populacao_exposta' => $this->nullableText($input['populacao_exposta'] ?? null),
                    'infraestruturas_expostas' => $this->nullableText($input['infraestruturas_expostas'] ?? null),
                    'vulnerabilidades_identificadas' => $this->nullableText($input['vulnerabilidades_identificadas'] ?? null),
                    'capacidade_local_resposta' => $this->nullableText($input['capacidade_local_resposta'] ?? null),
                    'probabilidade_evento' => $this->nullableText($input['probabilidade_evento'] ?? null),
                    'impacto_potencial' => $this->nullableText($input['impacto_potencial'] ?? null),
                    'nivel_risco' => $this->sanitizeEnum((string) ($input['nivel_risco'] ?? ''), ['BAIXO', 'MODERADO', 'ALTO', 'MUITO_ALTO'], ''),
                    'fatores_agravantes' => $this->nullableText($input['fatores_agravantes'] ?? null),
                    'fatores_atenuantes' => $this->nullableText($input['fatores_atenuantes'] ?? null),
                    'fontes_informacao_utilizadas' => $this->nullableText($input['fontes_informacao_utilizadas'] ?? null),
                    'responsavel_analise' => $this->nullableText($input['responsavel_analise'] ?? $auth['nome_completo'] ?? null),
                    'data_analise' => $this->sanitizeDate($input['data_analise'] ?? null),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Risco do PLANCON registrado com sucesso.',
            failureMessage: 'Falha ao registrar risco do PLANCON.'
        );
    }

    public function createScenario(array $auth, array $input, Request $request): array
    {
        return $this->createPlanconChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'PLANCON_CENARIO_CREATE',
            entityType: 'plancon_cenarios',
            requiredField: 'nome_cenario',
            secondaryRequiredField: 'descricao_cenario',
            handler: function (array $plancon, array $input, array $auth): int {
                return ($this->planconRepository ?? new PlanconRepository())->createScenario([
                    'plancon_id' => (int) $plancon['id'],
                    'conta_id' => (int) $plancon['conta_id'],
                    'orgao_id' => (int) $plancon['orgao_id'],
                    'unidade_id' => $plancon['unidade_id'] !== null ? (int) $plancon['unidade_id'] : null,
                    'nome_cenario' => (string) $this->requiredText($input['nome_cenario']),
                    'tipo_desastre_associado' => $this->nullableText($input['tipo_desastre_associado'] ?? null),
                    'descricao_cenario' => (string) $this->requiredText($input['descricao_cenario']),
                    'evento_disparador' => $this->nullableText($input['evento_disparador'] ?? null),
                    'area_afetada_estimada' => $this->nullableText($input['area_afetada_estimada'] ?? null),
                    'populacao_potencialmente_afetada' => $this->nullableText($input['populacao_potencialmente_afetada'] ?? null),
                    'danos_humanos_esperados' => $this->nullableText($input['danos_humanos_esperados'] ?? null),
                    'danos_materiais_esperados' => $this->nullableText($input['danos_materiais_esperados'] ?? null),
                    'danos_ambientais_esperados' => $this->nullableText($input['danos_ambientais_esperados'] ?? null),
                    'danos_sociais_esperados' => $this->nullableText($input['danos_sociais_esperados'] ?? null),
                    'servicos_interrompidos' => $this->nullableText($input['servicos_interrompidos'] ?? null),
                    'tempo_evolucao_evento' => $this->nullableText($input['tempo_evolucao_evento'] ?? null),
                    'necessidades_iniciais' => $this->nullableText($input['necessidades_iniciais'] ?? null),
                    'prioridades_operacionais' => $this->nullableText($input['prioridades_operacionais'] ?? null),
                    'classificacao_cenario' => $this->sanitizeEnum((string) ($input['classificacao_cenario'] ?? ''), ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'], ''),
                    'observacoes_cenario' => $this->nullableText($input['observacoes_cenario'] ?? null),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Cenario do PLANCON registrado com sucesso.',
            failureMessage: 'Falha ao registrar cenario do PLANCON.'
        );
    }

    public function createActivationLevel(array $auth, array $input, Request $request): array
    {
        return $this->createPlanconChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'PLANCON_ATIVACAO_CREATE',
            entityType: 'plancon_niveis_ativacao',
            requiredField: 'nivel_operacional',
            handler: function (array $plancon, array $input, array $auth): int {
                return ($this->planconRepository ?? new PlanconRepository())->createActivationLevel([
                    'plancon_id' => (int) $plancon['id'],
                    'conta_id' => (int) $plancon['conta_id'],
                    'orgao_id' => (int) $plancon['orgao_id'],
                    'unidade_id' => $plancon['unidade_id'] !== null ? (int) $plancon['unidade_id'] : null,
                    'nivel_operacional' => (string) $this->requiredText($input['nivel_operacional']),
                    'criterios_ativacao' => $this->nullableText($input['criterios_ativacao'] ?? null),
                    'gatilhos_acionamento' => $this->nullableText($input['gatilhos_acionamento'] ?? null),
                    'autoridade_responsavel' => $this->nullableText($input['autoridade_responsavel'] ?? null),
                    'acoes_automaticas' => $this->nullableText($input['acoes_automaticas'] ?? null),
                    'procedimentos_escalonamento' => $this->nullableText($input['procedimentos_escalonamento'] ?? null),
                    'status_nivel' => $this->sanitizeEnum((string) ($input['status_nivel'] ?? 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO'),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Nivel de ativacao registrado com sucesso.',
            failureMessage: 'Falha ao registrar nivel de ativacao.'
        );
    }

    public function createResource(array $auth, array $input, Request $request): array
    {
        return $this->createPlanconChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'PLANCON_RECURSO_CREATE',
            entityType: 'plancon_recursos',
            requiredField: 'tipo_recurso',
            secondaryRequiredField: 'descricao_recurso',
            handler: function (array $plancon, array $input, array $auth): int {
                return ($this->planconRepository ?? new PlanconRepository())->createResource([
                    'plancon_id' => (int) $plancon['id'],
                    'conta_id' => (int) $plancon['conta_id'],
                    'orgao_id' => (int) $plancon['orgao_id'],
                    'unidade_id' => $plancon['unidade_id'] !== null ? (int) $plancon['unidade_id'] : null,
                    'tipo_recurso' => (string) $this->requiredText($input['tipo_recurso']),
                    'categoria_recurso' => $this->nullableText($input['categoria_recurso'] ?? null),
                    'descricao_recurso' => (string) $this->requiredText($input['descricao_recurso']),
                    'quantidade_disponivel' => $this->nullableDecimal($input['quantidade_disponivel'] ?? null),
                    'unidade_medida' => $this->nullableText($input['unidade_medida'] ?? null),
                    'localizacao_base' => $this->nullableText($input['localizacao_base'] ?? null),
                    'tempo_mobilizacao' => $this->nullableText($input['tempo_mobilizacao'] ?? null),
                    'status_recurso' => $this->sanitizeEnum(
                        (string) ($input['status_recurso'] ?? 'DISPONIVEL'),
                        ['DISPONIVEL', 'INDISPONIVEL', 'EM_MANUTENCAO'],
                        'DISPONIVEL'
                    ),
                    'responsavel_recurso' => $this->nullableText($input['responsavel_recurso'] ?? null),
                    'contato_responsavel' => $this->nullableText($input['contato_responsavel'] ?? null),
                    'observacoes' => $this->nullableText($input['observacoes'] ?? null),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Recurso do PLANCON registrado com sucesso.',
            failureMessage: 'Falha ao registrar recurso do PLANCON.'
        );
    }

    public function createReview(array $auth, array $input, Request $request): array
    {
        return $this->createPlanconChild(
            auth: $auth,
            input: $input,
            request: $request,
            action: 'PLANCON_REVISAO_CREATE',
            entityType: 'plancon_revisoes',
            requiredField: 'versao_revisao',
            handler: function (array $plancon, array $input, array $auth): int {
                return ($this->planconRepository ?? new PlanconRepository())->createReview([
                    'plancon_id' => (int) $plancon['id'],
                    'conta_id' => (int) $plancon['conta_id'],
                    'orgao_id' => (int) $plancon['orgao_id'],
                    'unidade_id' => $plancon['unidade_id'] !== null ? (int) $plancon['unidade_id'] : null,
                    'versao_revisao' => (string) $this->requiredText($input['versao_revisao']),
                    'motivo_revisao' => $this->nullableText($input['motivo_revisao'] ?? null),
                    'alteracoes_realizadas' => $this->nullableText($input['alteracoes_realizadas'] ?? null),
                    'pendencias' => $this->nullableText($input['pendencias'] ?? null),
                    'data_revisao' => $this->sanitizeDate($input['data_revisao'] ?? null),
                    'proxima_revisao' => $this->sanitizeDate($input['proxima_revisao'] ?? null),
                    'aprovado_por' => $this->nullableText($input['aprovado_por'] ?? null),
                    'status_revisao' => $this->sanitizeEnum(
                        (string) ($input['status_revisao'] ?? 'RASCUNHO'),
                        ['RASCUNHO', 'EM_ANALISE', 'APROVADA', 'REPROVADA'],
                        'RASCUNHO'
                    ),
                    'registrado_por_usuario_id' => (int) $auth['usuario_id'],
                ]);
            },
            successMessage: 'Revisao do PLANCON registrada com sucesso.',
            failureMessage: 'Falha ao registrar revisao do PLANCON.'
        );
    }

    private function createPlanconChild(
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
        if (!$this->canManagePlancon($auth)) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode alterar blocos do PLANCON.'];
        }

        $planconId = (int) ($input['plancon_id'] ?? 0);
        if ($planconId < 1) {
            return ['ok' => false, 'message' => 'Informe um PLANCON valido.'];
        }

        if ($this->requiredText($input[$requiredField] ?? null) === null) {
            return ['ok' => false, 'message' => 'Preencha os campos obrigatorios do bloco informado.'];
        }
        if ($secondaryRequiredField !== null && $this->requiredText($input[$secondaryRequiredField] ?? null) === null) {
            return ['ok' => false, 'message' => 'Preencha os campos obrigatorios do bloco informado.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para registrar bloco do PLANCON.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $plancon = ($this->planconRepository ?? new PlanconRepository())->findPlanconById($scope, $planconId);
        if ($plancon === null) {
            return ['ok' => false, 'message' => 'PLANCON nao encontrado no seu escopo de acesso.'];
        }

        try {
            $id = $handler($plancon, $input, $auth);
            $this->audit($auth, $request, $action, $entityType, $id, ['plancon_id' => $planconId]);
            return ['ok' => true, 'message' => $successMessage];
        } catch (Throwable) {
            return ['ok' => false, 'message' => $failureMessage];
        }
    }

    private function canManagePlancon(array $auth): bool
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        return ($this->operationalPolicy ?? new OperationalPolicy())->canManagePlancon($profiles);
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
            'modulo_codigo' => 'PLANCON',
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

    private function nullableDecimal(mixed $value): ?string
    {
        $raw = str_replace(',', '.', trim((string) $value));
        if ($raw === '' || !is_numeric($raw)) {
            return null;
        }

        return number_format((float) $raw, 2, '.', '');
    }

    private function sanitizeDate(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) !== 1) {
            return null;
        }

        return $raw;
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function normalizeMunicipioValue(mixed $municipio, mixed $ufSigla): ?string
    {
        $nomeMunicipio = $this->nullableText($municipio);
        if ($nomeMunicipio === null) {
            return null;
        }

        $uf = BrazilUf::normalize($ufSigla);
        if ($uf === null) {
            return $nomeMunicipio;
        }

        if (preg_match('/\/[A-Z]{2}$/', $nomeMunicipio) === 1) {
            return $nomeMunicipio;
        }

        return $nomeMunicipio . '/' . $uf;
    }
}
