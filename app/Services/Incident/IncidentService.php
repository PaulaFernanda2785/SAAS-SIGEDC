<?php

declare(strict_types=1);

namespace App\Services\Incident;

use App\Domain\Enum\BrazilUf;
use App\Policies\OperationalPolicy;
use App\Repositories\Operational\IncidentRepository;
use App\Repositories\Operational\UnitRepository;
use App\Services\Audit\AuditService;
use App\Services\Institutional\ScopeService;
use App\Support\Request;
use Throwable;

final class IncidentService
{
    public function __construct(
        private readonly ?IncidentRepository $incidentRepository = null,
        private readonly ?UnitRepository $unitRepository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?OperationalPolicy $operationalPolicy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function dashboardData(array $auth): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return [
                'summary' => [
                    'incidentes_abertos' => 0,
                    'incidentes_em_andamento' => 0,
                    'incidentes_controlados' => 0,
                    'incidentes_encerrados' => 0,
                    'total_incidentes' => 0,
                    'periodos_ativos' => 0,
                    'registros_24h' => 0,
                ],
                'recent_incidents' => [],
                'scope' => $scopeService->scopeFilter($auth),
            ];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->incidentRepository ?? new IncidentRepository();

        return [
            'summary' => $repository->dashboardSummary($scope),
            'recent_incidents' => $repository->recentIncidents($scope, 10),
            'scope' => $scope,
        ];
    }

    public function workspaceData(array $auth): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'incidents' => [],
                'incident_options' => [],
                'period_options' => [],
                'unit_options' => [],
                'recent_records' => [],
                'status_options' => ['ABERTO', 'EM_ANDAMENTO', 'CONTROLADO', 'ENCERRADO'],
                'command_status_options' => ['ATIVO', 'TRANSFERIDO', 'ENCERRADO'],
                'period_status_options' => ['PLANEJADO', 'ATIVO', 'ENCERRADO'],
                'record_type_options' => ['DECISAO', 'ACIONAMENTO', 'OCORRENCIA', 'MOBILIZACAO', 'ATUALIZACAO', 'COMUNICADO'],
                'record_status_options' => ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO'],
                'criticality_options' => ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'],
                'classification_options' => ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'],
            ];
        }

        $repository = $this->incidentRepository ?? new IncidentRepository();
        $unitRepository = $this->unitRepository ?? new UnitRepository();

        return [
            'scope' => $scope,
            'incidents' => $repository->incidentsForWorkspace($scope),
            'incident_options' => $repository->incidentOptions($scope),
            'period_options' => $repository->periodOptions($scope),
            'unit_options' => $unitRepository->optionsByScope($scope),
            'recent_records' => $repository->recentRecords($scope, 35),
            'status_options' => ['ABERTO', 'EM_ANDAMENTO', 'CONTROLADO', 'ENCERRADO'],
            'command_status_options' => ['ATIVO', 'TRANSFERIDO', 'ENCERRADO'],
            'period_status_options' => ['PLANEJADO', 'ATIVO', 'ENCERRADO'],
            'record_type_options' => ['DECISAO', 'ACIONAMENTO', 'OCORRENCIA', 'MOBILIZACAO', 'ATUALIZACAO', 'COMUNICADO'],
            'record_status_options' => ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO'],
            'criticality_options' => ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'],
            'classification_options' => ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'],
        ];
    }

    public function openIncident(array $auth, array $input, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->operationalPolicy ?? new OperationalPolicy())->canOpenIncident($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode abrir incidentes.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $nomeIncidente = $this->requiredText($input['nome_incidente'] ?? null);
        $tipoOcorrencia = $this->requiredText($input['tipo_ocorrencia'] ?? null);
        $descricaoInicial = $this->requiredText($input['descricao_inicial'] ?? null);
        if ($nomeIncidente === null || $tipoOcorrencia === null || $descricaoInicial === null) {
            return ['ok' => false, 'message' => 'Nome, tipo e descricao inicial sao obrigatorios.'];
        }

        $statusIncidente = $this->sanitizeEnum(
            (string) ($input['status_incidente'] ?? 'ABERTO'),
            ['ABERTO', 'EM_ANDAMENTO', 'CONTROLADO', 'ENCERRADO'],
            'ABERTO'
        );
        $classificacao = $this->sanitizeEnum((string) ($input['classificacao_inicial'] ?? ''), ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'], '');
        $classificacao = $classificacao === '' ? null : $classificacao;

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
        if (!$scopeService->canWriteToUnit($auth, $targetUnitId)) {
            return ['ok' => false, 'message' => 'Escopo atual nao permite gravar em outra unidade.'];
        }

        $numeroOcorrencia = trim((string) ($input['numero_ocorrencia'] ?? ''));
        if ($numeroOcorrencia === '') {
            $numeroOcorrencia = $this->generateIncidentCode((int) ($auth['orgao_id'] ?? 0));
        }

        $dataHoraAbertura = $this->parseDateTimeInput($input['data_hora_abertura'] ?? null) ?? date('Y-m-d H:i:s');

        try {
            $id = ($this->incidentRepository ?? new IncidentRepository())->createIncident([
                'conta_id' => (int) $auth['conta_id'],
                'orgao_id' => (int) $auth['orgao_id'],
                'unidade_id' => $targetUnitId,
                'numero_ocorrencia' => $numeroOcorrencia,
                'nome_incidente' => $nomeIncidente,
                'tipo_ocorrencia' => strtoupper($tipoOcorrencia),
                'classificacao_inicial' => $classificacao,
                'data_hora_acionamento' => $this->parseDateTimeInput($input['data_hora_acionamento'] ?? null),
                'data_hora_abertura' => $dataHoraAbertura,
                'municipio' => $this->normalizeMunicipioValue(
                    $input['municipio'] ?? null,
                    $input['uf_sigla_referencia'] ?? ($auth['uf_sigla'] ?? null)
                ),
                'local_detalhado' => $this->nullableText($input['local_detalhado'] ?? null),
                'coordenadas' => $this->nullableText($input['coordenadas'] ?? null),
                'orgao_primeira_informacao' => $this->nullableText($input['orgao_primeira_informacao'] ?? null),
                'canal_recebimento' => $this->nullableText($input['canal_recebimento'] ?? null),
                'comunicante' => $this->nullableText($input['comunicante'] ?? null),
                'descricao_inicial' => $descricaoInicial,
                'situacao_inicial_observada' => $this->nullableText($input['situacao_inicial_observada'] ?? null),
                'populacao_potencialmente_afetada' => $this->nullableInt($input['populacao_potencialmente_afetada'] ?? null),
                'danos_humanos_iniciais' => $this->nullableText($input['danos_humanos_iniciais'] ?? null),
                'danos_materiais_iniciais' => $this->nullableText($input['danos_materiais_iniciais'] ?? null),
                'danos_ambientais_iniciais' => $this->nullableText($input['danos_ambientais_iniciais'] ?? null),
                'riscos_imediatos' => $this->nullableText($input['riscos_imediatos'] ?? null),
                'orgao_lider_inicial' => $this->nullableText($input['orgao_lider_inicial'] ?? null),
                'status_incidente' => $statusIncidente,
                'plancon_id' => $this->nullableInt($input['plancon_id'] ?? null),
                'cenario_id' => $this->nullableInt($input['cenario_id'] ?? null),
                'aberto_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'INCIDENTE_CREATE', 'incidentes', $id, [
                'numero_ocorrencia' => $numeroOcorrencia,
                'status_incidente' => $statusIncidente,
                'escopo_ativo' => $scope['escopo_ativo'] ?? null,
            ]);

            return ['ok' => true, 'message' => 'Incidente aberto com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao abrir incidente. Verifique numero de ocorrencia duplicado.'];
        }
    }

    public function registerBriefing(array $auth, array $input, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->operationalPolicy ?? new OperationalPolicy())->canRegisterBriefing($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode registrar briefing.'];
        }

        $incidentId = (int) ($input['incidente_id'] ?? 0);
        $resumoSituacao = $this->requiredText($input['resumo_situacao'] ?? null);
        if ($incidentId < 1 || $resumoSituacao === null) {
            return ['ok' => false, 'message' => 'Informe incidente e resumo da situacao.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->incidentRepository ?? new IncidentRepository();
        $incident = $repository->findIncidentById($scope, $incidentId);
        if ($incident === null) {
            return ['ok' => false, 'message' => 'Incidente nao encontrado no seu escopo de acesso.'];
        }
        if ((string) ($incident['status_incidente'] ?? '') === 'ENCERRADO') {
            return ['ok' => false, 'message' => 'Incidente encerrado nao aceita novo briefing operacional.'];
        }

        $version = $repository->nextBriefingVersion($incidentId);

        try {
            $id = $repository->createBriefing([
                'incidente_id' => $incidentId,
                'conta_id' => (int) $incident['conta_id'],
                'orgao_id' => (int) $incident['orgao_id'],
                'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                'versao_briefing' => $version,
                'resumo_situacao' => $resumoSituacao,
                'eventos_significativos' => $this->nullableText($input['eventos_significativos'] ?? null),
                'objetivos_iniciais' => $this->nullableText($input['objetivos_iniciais'] ?? null),
                'acoes_atuais' => $this->nullableText($input['acoes_atuais'] ?? null),
                'recursos_alocados' => $this->nullableText($input['recursos_alocados'] ?? null),
                'recursos_solicitados' => $this->nullableText($input['recursos_solicitados'] ?? null),
                'riscos_criticos_seguranca' => $this->nullableText($input['riscos_criticos_seguranca'] ?? null),
                'restricoes_operacionais' => $this->nullableText($input['restricoes_operacionais'] ?? null),
                'necessidades_imediatas' => $this->nullableText($input['necessidades_imediatas'] ?? null),
                'responsavel_briefing' => $this->nullableText($input['responsavel_briefing'] ?? $auth['nome_completo'] ?? null),
                'data_hora_briefing' => $this->parseDateTimeInput($input['data_hora_briefing'] ?? null) ?? date('Y-m-d H:i:s'),
                'uso_transferencia_comando' => !empty($input['uso_transferencia_comando']) ? 1 : 0,
                'observacoes' => $this->nullableText($input['observacoes'] ?? null),
                'registrado_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'INCIDENTE_BRIEFING_CREATE', 'incidentes_briefing', $id, [
                'incidente_id' => $incidentId,
                'versao_briefing' => $version,
            ]);

            return ['ok' => true, 'message' => 'Briefing registrado com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao registrar briefing do incidente.'];
        }
    }

    public function upsertCommand(array $auth, array $input, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->operationalPolicy ?? new OperationalPolicy())->canManageCommand($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode registrar comando inicial.'];
        }

        $incidentId = (int) ($input['incidente_id'] ?? 0);
        if ($incidentId < 1) {
            return ['ok' => false, 'message' => 'Informe o incidente para registrar o comando.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->incidentRepository ?? new IncidentRepository();
        $incident = $repository->findIncidentById($scope, $incidentId);
        if ($incident === null) {
            return ['ok' => false, 'message' => 'Incidente nao encontrado no seu escopo de acesso.'];
        }
        if ((string) ($incident['status_incidente'] ?? '') === 'ENCERRADO') {
            return ['ok' => false, 'message' => 'Incidente encerrado nao aceita alteracao de comando inicial.'];
        }

        $tipoComando = $this->sanitizeEnum((string) ($input['tipo_comando'] ?? 'UNICO'), ['UNICO', 'UNIFICADO'], 'UNICO');
        $statusComando = $this->sanitizeEnum((string) ($input['status_comando'] ?? 'ATIVO'), ['ATIVO', 'TRANSFERIDO', 'ENCERRADO'], 'ATIVO');

        try {
            $repository->upsertCommand([
                'incidente_id' => $incidentId,
                'conta_id' => (int) $incident['conta_id'],
                'orgao_id' => (int) $incident['orgao_id'],
                'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                'tipo_comando' => $tipoComando,
                'comandante_usuario_id' => $this->nullableInt($input['comandante_usuario_id'] ?? null),
                'comandante_nome' => $this->nullableText($input['comandante_nome'] ?? null),
                'instituicao_comandante' => $this->nullableText($input['instituicao_comandante'] ?? null),
                'autoridade_legal' => $this->nullableText($input['autoridade_legal'] ?? null),
                'comando_unificado' => $this->nullableText($input['comando_unificado'] ?? null),
                'data_hora_assuncao' => $this->parseDateTimeInput($input['data_hora_assuncao'] ?? null) ?? date('Y-m-d H:i:s'),
                'data_hora_transferencia' => $this->parseDateTimeInput($input['data_hora_transferencia'] ?? null),
                'motivo_transferencia' => $this->nullableText($input['motivo_transferencia'] ?? null),
                'base_legal_ativacao' => $this->nullableText($input['base_legal_ativacao'] ?? null),
                'local_posto_comando' => $this->nullableText($input['local_posto_comando'] ?? null),
                'status_comando' => $statusComando,
                'diretrizes_institucionais' => $this->nullableText($input['diretrizes_institucionais'] ?? null),
                'restricoes_juridicas_operacionais' => $this->nullableText($input['restricoes_juridicas_operacionais'] ?? null),
                'observacoes' => $this->nullableText($input['observacoes'] ?? null),
                'atualizado_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'INCIDENTE_COMANDO_UPSERT', 'incidentes_comando', $incidentId, [
                'incidente_id' => $incidentId,
                'tipo_comando' => $tipoComando,
                'status_comando' => $statusComando,
            ]);

            return ['ok' => true, 'message' => 'Comando inicial atualizado com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao atualizar comando inicial do incidente.'];
        }
    }

    public function createPeriod(array $auth, array $input, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->operationalPolicy ?? new OperationalPolicy())->canCreatePeriod($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode abrir periodo operacional.'];
        }

        $incidentId = (int) ($input['incidente_id'] ?? 0);
        if ($incidentId < 1) {
            return ['ok' => false, 'message' => 'Informe o incidente para criar o periodo operacional.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->incidentRepository ?? new IncidentRepository();
        $incident = $repository->findIncidentById($scope, $incidentId);
        if ($incident === null) {
            return ['ok' => false, 'message' => 'Incidente nao encontrado no seu escopo de acesso.'];
        }
        if ((string) ($incident['status_incidente'] ?? '') === 'ENCERRADO') {
            return ['ok' => false, 'message' => 'Incidente encerrado nao permite novo periodo operacional.'];
        }

        $numeroPeriodo = $this->nullableInt($input['numero_periodo'] ?? null) ?? $repository->nextPeriodNumber($incidentId);
        $statusPeriodo = $this->sanitizeEnum((string) ($input['status_periodo'] ?? 'ATIVO'), ['PLANEJADO', 'ATIVO', 'ENCERRADO'], 'ATIVO');

        try {
            $id = $repository->createPeriod([
                'incidente_id' => $incidentId,
                'conta_id' => (int) $incident['conta_id'],
                'orgao_id' => (int) $incident['orgao_id'],
                'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                'numero_periodo' => $numeroPeriodo,
                'data_hora_inicio' => $this->parseDateTimeInput($input['data_hora_inicio'] ?? null) ?? date('Y-m-d H:i:s'),
                'data_hora_fim' => $this->parseDateTimeInput($input['data_hora_fim'] ?? null),
                'situacao_inicial_periodo' => $this->nullableText($input['situacao_inicial_periodo'] ?? null),
                'objetivos_periodo' => $this->nullableText($input['objetivos_periodo'] ?? null),
                'recursos_principais_periodo' => $this->nullableText($input['recursos_principais_periodo'] ?? null),
                'briefing_realizado' => !empty($input['briefing_realizado']) ? 1 : 0,
                'pai_vinculado' => $this->nullableText($input['pai_vinculado'] ?? null),
                'situacao_encerramento' => $this->nullableText($input['situacao_encerramento'] ?? null),
                'pendencias' => $this->nullableText($input['pendencias'] ?? null),
                'responsavel_aprovacao' => $this->nullableText($input['responsavel_aprovacao'] ?? null),
                'status_periodo' => $statusPeriodo,
                'registrado_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'INCIDENTE_PERIODO_CREATE', 'incidentes_periodos_operacionais', $id, [
                'incidente_id' => $incidentId,
                'numero_periodo' => $numeroPeriodo,
            ]);

            return ['ok' => true, 'message' => 'Periodo operacional registrado com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao registrar periodo operacional.'];
        }
    }

    public function createRecord(array $auth, array $input, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->operationalPolicy ?? new OperationalPolicy())->canCreateRecord($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao pode registrar diario operacional.'];
        }

        $incidentId = (int) ($input['incidente_id'] ?? 0);
        $tipoRegistro = $this->sanitizeEnum(
            (string) ($input['tipo_registro'] ?? 'ATUALIZACAO'),
            ['DECISAO', 'ACIONAMENTO', 'OCORRENCIA', 'MOBILIZACAO', 'ATUALIZACAO', 'COMUNICADO'],
            'ATUALIZACAO'
        );
        $tituloRegistro = $this->requiredText($input['titulo_registro'] ?? null);
        $descricaoRegistro = $this->requiredText($input['descricao_registro'] ?? null);

        if ($incidentId < 1 || $tituloRegistro === null || $descricaoRegistro === null) {
            return ['ok' => false, 'message' => 'Incidente, titulo e descricao do registro sao obrigatorios.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para operacao.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->incidentRepository ?? new IncidentRepository();
        $incident = $repository->findIncidentById($scope, $incidentId);
        if ($incident === null) {
            return ['ok' => false, 'message' => 'Incidente nao encontrado no seu escopo de acesso.'];
        }
        if ((string) ($incident['status_incidente'] ?? '') === 'ENCERRADO') {
            return ['ok' => false, 'message' => 'Incidente encerrado nao permite novo registro operacional.'];
        }

        $periodId = $this->nullableInt($input['periodo_operacional_id'] ?? null);
        if ($periodId !== null && !$repository->periodBelongsToIncident($scope, $periodId, $incidentId)) {
            return ['ok' => false, 'message' => 'Periodo operacional informado nao pertence ao incidente/escopo atual.'];
        }

        $statusRegistro = $this->sanitizeEnum((string) ($input['status_registro'] ?? 'ABERTO'), ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO'], 'ABERTO');
        $criticidade = $this->sanitizeEnum((string) ($input['criticidade'] ?? 'MODERADA'), ['BAIXA', 'MODERADA', 'ALTA', 'CRITICA'], 'MODERADA');

        $dadosJson = null;
        $dadosExtras = $this->nullableText($input['dados_extras_json'] ?? null);
        if ($dadosExtras !== null) {
            json_decode($dadosExtras, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $dadosJson = $dadosExtras;
            }
        }

        try {
            $id = $repository->createRecord([
                'incidente_id' => $incidentId,
                'periodo_operacional_id' => $periodId,
                'conta_id' => (int) $incident['conta_id'],
                'orgao_id' => (int) $incident['orgao_id'],
                'unidade_id' => $incident['unidade_id'] !== null ? (int) $incident['unidade_id'] : null,
                'data_hora_registro' => $this->parseDateTimeInput($input['data_hora_registro'] ?? null) ?? date('Y-m-d H:i:s'),
                'tipo_registro' => $tipoRegistro,
                'titulo_registro' => $tituloRegistro,
                'descricao_registro' => $descricaoRegistro,
                'origem_informacao' => $this->nullableText($input['origem_informacao'] ?? null),
                'responsavel_lancamento' => $this->nullableText($input['responsavel_lancamento'] ?? $auth['nome_completo'] ?? null),
                'encaminhamento' => $this->nullableText($input['encaminhamento'] ?? null),
                'status_registro' => $statusRegistro,
                'criticidade' => $criticidade,
                'dados_json' => $dadosJson,
                'registrado_por_usuario_id' => (int) $auth['usuario_id'],
            ]);

            $this->audit($auth, $request, 'INCIDENTE_REGISTRO_CREATE', 'incidentes_registros_operacionais', $id, [
                'incidente_id' => $incidentId,
                'periodo_operacional_id' => $periodId,
                'tipo_registro' => $tipoRegistro,
                'criticidade' => $criticidade,
            ]);

            return ['ok' => true, 'message' => 'Registro operacional salvo com sucesso.'];
        } catch (Throwable) {
            return ['ok' => false, 'message' => 'Falha ao salvar registro operacional.'];
        }
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
            'modulo_codigo' => 'INCIDENTES',
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

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
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

    private function generateIncidentCode(int $orgaoId): string
    {
        $orgao = $orgaoId > 0 ? (string) $orgaoId : 'X';
        return sprintf('INC-%s-%s-%03d', $orgao, date('YmdHis'), random_int(1, 999));
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
