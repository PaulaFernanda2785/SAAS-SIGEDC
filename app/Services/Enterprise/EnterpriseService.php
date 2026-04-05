<?php

declare(strict_types=1);

namespace App\Services\Enterprise;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Policies\AdminEnterprisePolicy;
use App\Repositories\Enterprise\EnterpriseRepository;
use App\Repositories\SaaS\InstitutionRepository;
use App\Services\Audit\AuditService;
use App\Support\Request;
use DateInterval;
use DateTimeImmutable;

final class EnterpriseService
{
    public function __construct(
        private readonly ?EnterpriseRepository $enterpriseRepository = null,
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?AdminEnterprisePolicy $policy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function dashboardData(array $auth, array $filters): array
    {
        $currentUf = $this->resolveUfFilter($auth, $filters['uf'] ?? null);
        $repo = $this->enterpriseRepository ?? new EnterpriseRepository();
        $institutionRepo = $this->institutionRepository ?? new InstitutionRepository();
        $policy = $this->policy ?? new AdminEnterprisePolicy();

        return [
            'summary' => $repo->summary($currentUf),
            'feature_flags' => $repo->featureFlags($currentUf),
            'api_apps' => $repo->apiClientApps($currentUf),
            'integracoes' => $repo->integracoes($currentUf),
            'automacoes' => $repo->automacoes($currentUf),
            'sla_politicas' => $repo->slaPolicies($currentUf),
            'tickets' => $repo->suporteTickets($currentUf),
            'assinaturas_digitais' => $repo->digitalSignatures($currentUf),
            'relatorios_executivos' => $repo->executiveReports($currentUf),
            'options' => $institutionRepo->contextOptions($currentUf),
            'current_uf_filter' => $currentUf,
            'can_select_all_uf' => $this->isAdminMaster($auth),
            'capabilities' => [
                'access' => $policy->canAccess($this->profiles($auth)),
                'features' => $policy->canManageFeatures($this->profiles($auth)) && $this->hasModule($auth, 'ENTERPRISE_CORE'),
                'api' => $policy->canManageApi($this->profiles($auth)) && $this->hasModule($auth, 'API_ENTERPRISE'),
                'integracoes' => $policy->canManageIntegrations($this->profiles($auth)) && $this->hasModule($auth, 'INTEGRACOES_EXTERNAS'),
                'automacoes' => $policy->canManageAutomations($this->profiles($auth)) && $this->hasModule($auth, 'AUTOMACOES'),
                'sla' => $policy->canManageSla($this->profiles($auth)) && $this->hasModule($auth, 'SLA_SUPORTE'),
                'tickets' => $policy->canManageSupport($this->profiles($auth)) && $this->hasModule($auth, 'SLA_SUPORTE'),
                'assinatura_digital' => $policy->canRegisterDigitalSignature($this->profiles($auth)) && $this->hasModule($auth, 'ASSINATURA_DIGITAL'),
                'analytics' => $policy->canViewAnalytics($this->profiles($auth)) && $this->hasModule($auth, 'ANALYTICS_EXECUTIVO'),
            ],
        ];
    }

    public function authenticateApiToken(string $token, Request $request): array
    {
        $token = trim($token);
        if ($token === '') {
            return ['ok' => false, 'status' => 401, 'message' => 'Token de API ausente.'];
        }

        $repo = $this->enterpriseRepository ?? new EnterpriseRepository();
        $client = $repo->apiClientByTokenHash($this->hashApiToken($token));

        if ($client === null) {
            return ['ok' => false, 'status' => 401, 'message' => 'Token de API invalido ou expirado.'];
        }

        $contaId = (int) ($client['conta_id'] ?? 0);
        if ($contaId < 1 || !$repo->hasActiveModuleByConta($contaId, 'API_ENTERPRISE')) {
            $this->audit('API_ENTERPRISE', 'API_ACCESS_DENIED_MODULE', null, [
                'api_client_id' => $client['id'] ?? null,
                'reason' => 'modulo_api_enterprise_nao_liberado',
            ], $request, [
                'conta_id' => $client['conta_id'] ?? null,
                'orgao_id' => $client['orgao_id'] ?? null,
                'unidade_id' => $client['unidade_id'] ?? null,
                'usuario_id' => $client['criado_por_usuario_id'] ?? null,
            ], 'NEGADO');

            return ['ok' => false, 'status' => 403, 'message' => 'Acesso API indisponivel para a assinatura atual.'];
        }

        $repo->touchApiClientUsage((int) $client['id']);

        return [
            'ok' => true,
            'status' => 200,
            'message' => null,
            'api_auth' => [
                'api_client_id' => (int) $client['id'],
                'api_client_name' => (string) ($client['nome_app'] ?? ''),
                'token_prefix' => (string) ($client['token_prefix'] ?? ''),
                'conta_id' => (int) ($client['conta_id'] ?? 0),
                'orgao_id' => isset($client['orgao_id']) ? (int) $client['orgao_id'] : null,
                'unidade_id' => isset($client['unidade_id']) ? (int) $client['unidade_id'] : null,
                'uf_sigla' => BrazilUf::normalize($client['uf_sigla'] ?? null),
                'escopos' => is_array($client['escopos'] ?? null) ? $client['escopos'] : [],
                'gerado_por_usuario_id' => isset($client['criado_por_usuario_id']) ? (int) $client['criado_por_usuario_id'] : null,
            ],
        ];
    }

    public function apiExecutiveSummary(array $apiAuth, array $filters, bool $persist): array
    {
        $contaId = (int) ($apiAuth['conta_id'] ?? 0);
        if ($contaId < 1) {
            return ['ok' => false, 'status' => 401, 'message' => 'Contexto de API invalido.'];
        }

        $dateFrom = $this->normalizeDate($filters['data_inicio'] ?? null);
        $dateTo = $this->normalizeDate($filters['data_fim'] ?? null);
        $scope = [
            'conta_id' => $contaId,
            'orgao_id' => isset($apiAuth['orgao_id']) ? (int) $apiAuth['orgao_id'] : null,
            'unidade_id' => isset($apiAuth['unidade_id']) ? (int) $apiAuth['unidade_id'] : null,
        ];

        $repo = $this->enterpriseRepository ?? new EnterpriseRepository();
        $summary = $repo->executiveSummaryByScope($scope, $dateFrom, $dateTo);

        $reportId = null;
        if ($persist) {
            $geradoPor = isset($apiAuth['gerado_por_usuario_id']) ? (int) $apiAuth['gerado_por_usuario_id'] : null;
            if ($geradoPor !== null && $geradoPor > 0) {
                $reportId = $repo->createExecutiveReport([
                    'conta_id' => $scope['conta_id'],
                    'orgao_id' => $scope['orgao_id'],
                    'unidade_id' => $scope['unidade_id'],
                    'periodo_inicio' => $dateFrom,
                    'periodo_fim' => $dateTo,
                    'filtros' => ['data_inicio' => $dateFrom, 'data_fim' => $dateTo, 'fonte' => 'api'],
                    'resumo' => $summary,
                    'total_incidentes' => $summary['total_incidentes'] ?? 0,
                    'total_plancons' => $summary['total_plancons'] ?? 0,
                    'total_alertas_ativos' => $summary['total_alertas_ativos'] ?? 0,
                    'total_tickets_abertos' => $summary['total_tickets_abertos'] ?? 0,
                    'total_tickets_sla_vencido' => $summary['total_tickets_sla_vencido'] ?? 0,
                    'gerado_por_usuario_id' => $geradoPor,
                ]);
            }
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => null,
            'data' => [
                'scope' => $scope,
                'filters' => ['data_inicio' => $dateFrom, 'data_fim' => $dateTo],
                'summary' => $summary,
                'report_id' => $reportId,
            ],
        ];
    }

    public function registerFeature(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'features', 'ENTERPRISE_CORE');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $featureCode = strtoupper(trim((string) ($input['feature_code'] ?? '')));
        if ($featureCode === '' || preg_match('/^[A-Z0-9_]{3,80}$/', $featureCode) !== 1) {
            return ['ok' => false, 'message' => 'Feature code invalido. Use A-Z, 0-9 e _ (3-80).'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createFeatureFlag([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'feature_code' => $featureCode,
            'status_feature' => $this->enum((string) ($input['status_feature'] ?? 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            'plano_referencia' => $this->nullableText($input['plano_referencia'] ?? null),
            'configuracoes' => $this->parseJsonOrRaw($input['configuracoes_json'] ?? null),
            'habilitado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('ENTERPRISE', 'ENTERPRISE_FEATURE_UPSERT', $id, [
            'feature_code' => $featureCode,
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Feature flag registrada com sucesso.'];
    }

    public function createApiClientApp(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'api', 'API_ENTERPRISE');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $nomeApp = trim((string) ($input['nome_app'] ?? ''));
        if ($nomeApp === '') {
            return ['ok' => false, 'message' => 'Informe o nome da aplicacao cliente.'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $tokenData = $this->generateApiToken();
        ($this->enterpriseRepository ?? new EnterpriseRepository())->createApiClientApp([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'nome_app' => $nomeApp,
            'token_prefix' => $tokenData['prefix'],
            'token_hash' => $tokenData['hash'],
            'escopos' => $this->parseScopes($input['escopos'] ?? null),
            'limite_rpm' => $this->boundedInt($input['limite_rpm'] ?? null, 1, 10000, 600),
            'status_app' => $this->enum((string) ($input['status_app'] ?? 'ATIVA'), ['ATIVA', 'BLOQUEADA', 'REVOGADA'], 'ATIVA'),
            'expira_em' => $this->nullableDateTime($input['expira_em'] ?? null),
            'criado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('API_ENTERPRISE', 'API_CLIENT_CREATE', null, [
            'token_prefix' => $tokenData['prefix'],
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return [
            'ok' => true,
            'message' => 'Cliente API criado com sucesso. Token exibido uma unica vez.',
            'token_plain' => $tokenData['plain'],
            'token_prefix' => $tokenData['prefix'],
        ];
    }
    public function createIntegracao(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'integracoes', 'INTEGRACOES_EXTERNAS');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $nome = trim((string) ($input['nome_integracao'] ?? ''));
        $endpoint = trim((string) ($input['endpoint_url'] ?? ''));
        if ($nome === '' || $endpoint === '' || filter_var($endpoint, FILTER_VALIDATE_URL) === false) {
            return ['ok' => false, 'message' => 'Informe nome da integracao e URL valida.'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createIntegracao([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'nome_integracao' => $nome,
            'tipo_integracao' => $this->enum((string) ($input['tipo_integracao'] ?? 'WEBHOOK'), ['WEBHOOK', 'HTTP_API'], 'WEBHOOK'),
            'endpoint_url' => $endpoint,
            'auth_tipo' => $this->enum((string) ($input['auth_tipo'] ?? 'NENHUMA'), ['NENHUMA', 'BEARER', 'BASIC', 'HEADER'], 'NENHUMA'),
            'credencial_ref' => $this->nullableText($input['credencial_ref'] ?? null),
            'timeout_ms' => $this->boundedInt($input['timeout_ms'] ?? null, 500, 120000, 4000),
            'status_integracao' => $this->enum((string) ($input['status_integracao'] ?? 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            'configuracoes' => $this->parseJsonOrRaw($input['configuracoes_json'] ?? null),
            'criado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('INTEGRACOES', 'INTEGRACAO_CREATE', $id, [
            'nome_integracao' => $nome,
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Integracao cadastrada com sucesso.'];
    }

    public function createAutomacao(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'automacoes', 'AUTOMACOES');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $nome = trim((string) ($input['nome_regra'] ?? ''));
        $evento = strtoupper(trim((string) ($input['evento_codigo'] ?? '')));
        if ($nome === '' || $evento === '') {
            return ['ok' => false, 'message' => 'Informe nome e evento da regra de automacao.'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createAutomacao([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'nome_regra' => $nome,
            'evento_codigo' => $evento,
            'condicao' => $this->parseJsonOrRaw($input['condicao_json'] ?? null),
            'acao_tipo' => $this->enum((string) ($input['acao_tipo'] ?? 'DISPARAR_INTEGRACAO'), ['DISPARAR_INTEGRACAO', 'ABRIR_TICKET', 'GERAR_ALERTA'], 'DISPARAR_INTEGRACAO'),
            'acao_config' => $this->parseJsonOrRaw($input['acao_config_json'] ?? null),
            'status_regra' => $this->enum((string) ($input['status_regra'] ?? 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            'criado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('AUTOMACOES', 'AUTOMACAO_CREATE', $id, [
            'evento_codigo' => $evento,
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Regra de automacao cadastrada com sucesso.'];
    }

    public function createSlaPolicy(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'sla', 'SLA_SUPORTE');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $codigo = strtoupper(trim((string) ($input['codigo_sla'] ?? '')));
        $nome = trim((string) ($input['nome_sla'] ?? ''));
        if ($codigo === '' || $nome === '' || preg_match('/^[A-Z0-9_\-]{3,80}$/', $codigo) !== 1) {
            return ['ok' => false, 'message' => 'Informe codigo e nome validos para a politica SLA.'];
        }

        $respostaMin = $this->boundedInt($input['tempo_resposta_min'] ?? null, 1, 43200, 60);
        $resolucaoMin = $this->boundedInt($input['tempo_resolucao_min'] ?? null, 1, 43200, 240);

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createSlaPolicy([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'codigo_sla' => $codigo,
            'nome_sla' => $nome,
            'prioridade' => $this->enum((string) ($input['prioridade'] ?? 'MODERADA'), ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'], 'MODERADA'),
            'tempo_resposta_min' => $respostaMin,
            'tempo_resolucao_min' => $resolucaoMin,
            'status_sla' => $this->enum((string) ($input['status_sla'] ?? 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            'criado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('SLA_SUPORTE', 'SLA_POLICY_UPSERT', $id, [
            'codigo_sla' => $codigo,
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Politica SLA salva com sucesso.'];
    }

    public function createSupportTicket(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'tickets', 'SLA_SUPORTE');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $titulo = trim((string) ($input['titulo_ticket'] ?? ''));
        $descricao = trim((string) ($input['descricao_ticket'] ?? ''));
        if ($titulo === '' || $descricao === '') {
            return ['ok' => false, 'message' => 'Informe titulo e descricao do ticket.'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $slaId = $this->toNullableInt($input['sla_politica_id'] ?? null);
        $respostaLimite = null;
        $resolucaoLimite = null;

        if ($slaId !== null) {
            $sla = ($this->enterpriseRepository ?? new EnterpriseRepository())->slaPolicyById($slaId);
            if ($sla === null || (int) ($sla['conta_id'] ?? 0) !== (int) $context['conta_id']) {
                return ['ok' => false, 'message' => 'Politica SLA informada nao pertence ao contexto selecionado.'];
            }

            $base = new DateTimeImmutable('now');
            $respostaInterval = new DateInterval('PT' . (int) ($sla['tempo_resposta_min'] ?? 0) . 'M');
            $resolucaoInterval = new DateInterval('PT' . (int) ($sla['tempo_resolucao_min'] ?? 0) . 'M');
            $respostaLimite = $base->add($respostaInterval)->format('Y-m-d H:i:s');
            $resolucaoLimite = $base->add($resolucaoInterval)->format('Y-m-d H:i:s');
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createSuporteTicket([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'sla_politica_id' => $slaId,
            'titulo_ticket' => $titulo,
            'descricao_ticket' => $descricao,
            'prioridade' => $this->enum((string) ($input['prioridade'] ?? 'MODERADA'), ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'], 'MODERADA'),
            'status_ticket' => $this->enum((string) ($input['status_ticket'] ?? 'ABERTO'), ['ABERTO', 'EM_ATENDIMENTO', 'RESOLVIDO', 'FECHADO'], 'ABERTO'),
            'aberto_por_usuario_id' => $auth['usuario_id'] ?? null,
            'atribuido_para_usuario_id' => $this->toNullableInt($input['atribuido_para_usuario_id'] ?? null),
            'resposta_limite_em' => $respostaLimite,
            'resolucao_limite_em' => $resolucaoLimite,
        ]);

        $this->audit('SLA_SUPORTE', 'SUPORTE_TICKET_CREATE', $id, [
            'conta_id' => $context['conta_id'],
            'sla_politica_id' => $slaId,
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Ticket de suporte aberto com sucesso.'];
    }
    public function registerDigitalSignature(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'assinatura_digital', 'ASSINATURA_DIGITAL');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $entidadeTipo = strtolower(trim((string) ($input['entidade_tipo'] ?? '')));
        $entidadeId = (int) ($input['entidade_id'] ?? 0);
        $hashDocumento = strtolower(trim((string) ($input['hash_documento'] ?? '')));

        if ($entidadeTipo === '' || $entidadeId < 1 || preg_match('/^[a-f0-9]{64}$/', $hashDocumento) !== 1) {
            return ['ok' => false, 'message' => 'Informe entidade e hash SHA-256 valido (64 caracteres hex).'];
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $id = ($this->enterpriseRepository ?? new EnterpriseRepository())->createDigitalSignatureRecord([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'hash_documento' => $hashDocumento,
            'algoritmo_hash' => 'SHA256',
            'certificado_ref' => $this->nullableText($input['certificado_ref'] ?? null),
            'assinatura_payload' => $this->parseJsonOrRaw($input['assinatura_payload_json'] ?? null),
            'assinado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('ASSINATURA_DIGITAL', 'DIGITAL_SIGNATURE_REGISTER', $id, [
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'conta_id' => $context['conta_id'],
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Registro de assinatura digital salvo com sucesso.'];
    }

    public function generateExecutiveReport(array $auth, array $input, Request $request): array
    {
        $guard = $this->guard($auth, 'analytics', 'ANALYTICS_EXECUTIVO');
        if (($guard['ok'] ?? false) !== true) {
            return $guard;
        }

        $context = $this->resolveContext(
            $auth,
            (int) ($input['conta_id'] ?? 0),
            $this->toNullableInt($input['orgao_id'] ?? null),
            $this->toNullableInt($input['unidade_id'] ?? null)
        );
        if (($context['ok'] ?? false) !== true) {
            return $context;
        }

        $dateFrom = $this->normalizeDate($input['periodo_inicio'] ?? null);
        $dateTo = $this->normalizeDate($input['periodo_fim'] ?? null);

        $repo = $this->enterpriseRepository ?? new EnterpriseRepository();
        $summary = $repo->executiveSummaryByScope([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
        ], $dateFrom, $dateTo);

        $id = $repo->createExecutiveReport([
            'conta_id' => $context['conta_id'],
            'orgao_id' => $context['orgao_id'],
            'unidade_id' => $context['unidade_id'],
            'periodo_inicio' => $dateFrom,
            'periodo_fim' => $dateTo,
            'filtros' => ['periodo_inicio' => $dateFrom, 'periodo_fim' => $dateTo],
            'resumo' => $summary,
            'total_incidentes' => $summary['total_incidentes'] ?? 0,
            'total_plancons' => $summary['total_plancons'] ?? 0,
            'total_alertas_ativos' => $summary['total_alertas_ativos'] ?? 0,
            'total_tickets_abertos' => $summary['total_tickets_abertos'] ?? 0,
            'total_tickets_sla_vencido' => $summary['total_tickets_sla_vencido'] ?? 0,
            'gerado_por_usuario_id' => $auth['usuario_id'] ?? null,
        ]);

        $this->audit('ANALYTICS_EXECUTIVO', 'EXEC_REPORT_GENERATE', $id, [
            'conta_id' => $context['conta_id'],
            'periodo_inicio' => $dateFrom,
            'periodo_fim' => $dateTo,
        ], $request, $this->auditScopeFromContext($auth, $context));

        return ['ok' => true, 'message' => 'Relatorio executivo consolidado gerado com sucesso.'];
    }

    private function guard(array $auth, string $capability, string $moduleCode): array
    {
        $policy = $this->policy ?? new AdminEnterprisePolicy();
        $profiles = $this->profiles($auth);

        $can = match ($capability) {
            'features' => $policy->canManageFeatures($profiles),
            'api' => $policy->canManageApi($profiles),
            'integracoes' => $policy->canManageIntegrations($profiles),
            'automacoes' => $policy->canManageAutomations($profiles),
            'sla' => $policy->canManageSla($profiles),
            'tickets' => $policy->canManageSupport($profiles),
            'assinatura_digital' => $policy->canRegisterDigitalSignature($profiles),
            'analytics' => $policy->canViewAnalytics($profiles),
            default => false,
        };

        if (!$can) {
            return ['ok' => false, 'message' => 'Perfil sem permissao para esta operacao enterprise.'];
        }

        if (!$this->hasModule($auth, $moduleCode)) {
            return ['ok' => false, 'message' => 'Modulo contratado nao liberado para esta operacao.'];
        }

        return ['ok' => true];
    }

    private function resolveContext(array $auth, int $contaId, ?int $orgaoId, ?int $unidadeId): array
    {
        if ($contaId < 1) {
            return ['ok' => false, 'message' => 'Informe uma conta valida para a operacao.'];
        }

        $institutionRepo = $this->institutionRepository ?? new InstitutionRepository();
        $conta = $institutionRepo->accountById($contaId);
        if ($conta === null) {
            return ['ok' => false, 'message' => 'Conta informada nao encontrada.'];
        }

        $ufConta = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufConta)) {
            return ['ok' => false, 'message' => 'Operacao fora do UF de contexto permitido para o usuario atual.'];
        }

        if ($orgaoId !== null) {
            $orgao = $institutionRepo->orgaoById($orgaoId);
            if ($orgao === null || (int) ($orgao['conta_id'] ?? 0) !== $contaId) {
                return ['ok' => false, 'message' => 'Orgao informado nao pertence a conta selecionada.'];
            }
        }

        if ($unidadeId !== null) {
            $unidade = $institutionRepo->unidadeById($unidadeId);
            if ($unidade === null) {
                return ['ok' => false, 'message' => 'Unidade informada nao encontrada.'];
            }

            $orgaoDaUnidade = (int) ($unidade['orgao_id'] ?? 0);
            if ($orgaoId !== null && $orgaoDaUnidade !== $orgaoId) {
                return ['ok' => false, 'message' => 'Unidade nao pertence ao orgao informado.'];
            }
            if ($orgaoId === null) {
                $orgaoId = $orgaoDaUnidade > 0 ? $orgaoDaUnidade : null;
            }
        }

        return [
            'ok' => true,
            'conta_id' => $contaId,
            'orgao_id' => $orgaoId,
            'unidade_id' => $unidadeId,
            'uf_sigla' => $ufConta,
        ];
    }

    private function resolveUfFilter(array $auth, mixed $requestedUf): ?string
    {
        $ufRequested = BrazilUf::normalize($requestedUf);
        if ($this->isAdminMaster($auth)) {
            return $ufRequested;
        }

        return BrazilUf::normalize($auth['uf_sigla'] ?? null);
    }

    private function generateApiToken(): array
    {
        $prefixLength = (int) config('enterprise.api_prefix_length', 12);
        if ($prefixLength < 8) {
            $prefixLength = 12;
        }

        $prefix = strtoupper(substr(bin2hex(random_bytes(16)), 0, $prefixLength));
        $secret = bin2hex(random_bytes(24));
        $plain = sprintf('sigerd.%s.%s', strtolower($prefix), $secret);

        return [
            'prefix' => $prefix,
            'plain' => $plain,
            'hash' => $this->hashApiToken($plain),
        ];
    }

    private function hashApiToken(string $token): string
    {
        $pepper = (string) config('enterprise.api_token_pepper', '');
        return hash('sha256', $token . $pepper);
    }

    private function parseScopes(mixed $value): array
    {
        if (is_array($value)) {
            $scopes = $value;
        } else {
            $raw = trim((string) $value);
            if ($raw === '') {
                return [];
            }

            $scopes = preg_split('/[,;\s]+/', $raw) ?: [];
        }

        $normalized = [];
        foreach ($scopes as $scope) {
            $scope = strtoupper(trim((string) $scope));
            if ($scope !== '') {
                $normalized[] = $scope;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function parseJsonOrRaw(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return ['raw' => $text];
    }

    private function enum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function toNullableInt(mixed $value): ?int
    {
        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function boundedInt(mixed $value, int $min, int $max, int $default): int
    {
        $int = (int) $value;
        if ($int < $min) {
            return $default;
        }

        return min($int, $max);
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function normalizeDate(mixed $value): ?string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 ? $date : null;
    }

    private function nullableDateTime(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw) === 1) {
            return $raw . ' 23:59:59';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $raw) === 1) {
            return str_replace('T', ' ', $raw) . ':00';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}(:\d{2})?$/', $raw) === 1) {
            return strlen($raw) === 16 ? $raw . ':00' : $raw;
        }

        return null;
    }

    private function profiles(array $auth): array
    {
        return is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
    }

    private function isAdminMaster(array $auth): bool
    {
        return in_array(UserProfile::ADMIN_MASTER, $this->profiles($auth), true);
    }

    private function hasModule(array $auth, string $moduleCode): bool
    {
        $modules = is_array($auth['modulos_liberados'] ?? null) ? $auth['modulos_liberados'] : [];
        return in_array($moduleCode, $modules, true);
    }

    private function canOperateUf(array $auth, ?string $targetUf): bool
    {
        if ($this->isAdminMaster($auth)) {
            return true;
        }

        $userUf = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        if ($userUf === null || $targetUf === null) {
            return false;
        }

        return $userUf === $targetUf;
    }

    private function auditScopeFromContext(array $auth, array $context): array
    {
        return [
            'conta_id' => $context['conta_id'] ?? ($auth['conta_id'] ?? null),
            'orgao_id' => $context['orgao_id'] ?? ($auth['orgao_id'] ?? null),
            'unidade_id' => $context['unidade_id'] ?? ($auth['unidade_id'] ?? null),
            'usuario_id' => $auth['usuario_id'] ?? null,
        ];
    }

    private function audit(
        string $moduleCode,
        string $action,
        ?int $entityId,
        array $details,
        Request $request,
        array $scope,
        string $result = 'SUCESSO'
    ): void {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $scope['conta_id'] ?? null,
            'orgao_id' => $scope['orgao_id'] ?? null,
            'unidade_id' => $scope['unidade_id'] ?? null,
            'usuario_id' => $scope['usuario_id'] ?? null,
            'modulo_codigo' => $moduleCode,
            'acao' => $action,
            'resultado' => $result,
            'entidade_tipo' => 'enterprise',
            'entidade_id' => $entityId,
            'detalhes' => $details,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
