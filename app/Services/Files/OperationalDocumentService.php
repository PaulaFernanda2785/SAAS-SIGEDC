<?php

declare(strict_types=1);

namespace App\Services\Files;

use App\Policies\OperationalPolicy;
use App\Repositories\Operational\DocumentRepository;
use App\Repositories\Operational\IncidentRepository;
use App\Repositories\Plancon\PlanconRepository;
use App\Services\Audit\AuditService;
use App\Services\Institutional\ScopeService;
use App\Support\Request;
use Throwable;

final class OperationalDocumentService
{
    public function __construct(
        private readonly ?DocumentRepository $documentRepository = null,
        private readonly ?IncidentRepository $incidentRepository = null,
        private readonly ?PlanconRepository $planconRepository = null,
        private readonly ?ScopeService $scopeService = null,
        private readonly ?OperationalPolicy $policy = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function workspaceData(array $auth, array $filters): array
    {
        $scopeService = $this->scopeService ?? new ScopeService();
        $scope = $scopeService->scopeFilter($auth);
        $entityType = $this->sanitizeEntityType($filters['entidade_tipo'] ?? null);
        $normalizedFilters = ['entidade_tipo' => $entityType];

        if (!$scopeService->hasValidContext($auth)) {
            return [
                'scope' => $scope,
                'filters' => $normalizedFilters,
                'attachments' => [],
                'attachments_by_entity' => [],
                'incident_options' => [],
                'plancon_options' => [],
                'incident_record_options' => [],
                'plancon_risk_options' => [],
            ];
        }

        $repository = $this->documentRepository ?? new DocumentRepository();
        return [
            'scope' => $scope,
            'filters' => $normalizedFilters,
            'attachments' => $repository->attachmentsByScope($scope, $entityType, 180),
            'attachments_by_entity' => $repository->attachmentsByEntityType($scope),
            'incident_options' => $repository->incidentOptions($scope, 120),
            'plancon_options' => $repository->planconOptions($scope, 120),
            'incident_record_options' => $repository->incidentRecordOptions($scope, 120),
            'plancon_risk_options' => $repository->planconRiskOptions($scope, 120),
        ];
    }

    public function upload(array $auth, array $input, ?array $file, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        if (!(($this->policy ?? new OperationalPolicy())->canUploadDocuments($profiles))) {
            return ['ok' => false, 'message' => 'Seu perfil nao possui permissao para anexar documentos.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'message' => 'Contexto institucional invalido para anexar documento.'];
        }
        $scope = $scopeService->scopeFilter($auth);

        $resolvedEntity = $this->resolveEntity($input);
        if ($resolvedEntity === null) {
            return ['ok' => false, 'message' => 'Informe entidade de vinculo valida para o documento.'];
        }

        if (!$this->isEntityInScope($scope, $resolvedEntity['type'], $resolvedEntity['id'])) {
            return ['ok' => false, 'message' => 'Entidade informada nao pertence ao escopo institucional ativo.'];
        }

        if ($file === null || !isset($file['error'])) {
            return ['ok' => false, 'message' => 'Selecione um arquivo para envio.'];
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Falha no upload do arquivo. Tente novamente.'];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['ok' => false, 'message' => 'Arquivo temporario invalido para upload.'];
        }

        $sizeBytes = (int) ($file['size'] ?? 0);
        if ($sizeBytes < 1) {
            return ['ok' => false, 'message' => 'Arquivo enviado vazio.'];
        }

        $maxBytes = ((int) config('documents.max_size_mb', 12)) * 1024 * 1024;
        if ($maxBytes < 1) {
            $maxBytes = 12 * 1024 * 1024;
        }

        if ($sizeBytes > $maxBytes) {
            return ['ok' => false, 'message' => 'Arquivo excede o tamanho maximo permitido para upload.'];
        }

        $detectedMime = (string) (mime_content_type($tmpName) ?: '');
        $allowedMimes = config('documents.allowed_mimes', []);
        if (!is_array($allowedMimes) || !in_array($detectedMime, $allowedMimes, true)) {
            return ['ok' => false, 'message' => 'Tipo de arquivo nao permitido para anexos operacionais.'];
        }

        $originalName = $this->sanitizeFilename((string) ($file['name'] ?? 'documento.bin'));
        if ($originalName === '') {
            $originalName = 'documento.bin';
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $storageRelativePath = $this->buildStorageRelativePath($auth, $extension);
        $storageAbsolutePath = storage_path($storageRelativePath);
        $storageDirectory = dirname($storageAbsolutePath);
        if (!is_dir($storageDirectory) && !@mkdir($storageDirectory, 0775, true) && !is_dir($storageDirectory)) {
            return ['ok' => false, 'message' => 'Nao foi possivel preparar diretorio de anexos.'];
        }

        if (!@move_uploaded_file($tmpName, $storageAbsolutePath)) {
            return ['ok' => false, 'message' => 'Nao foi possivel salvar o arquivo enviado.'];
        }

        $hash = hash_file('sha256', $storageAbsolutePath) ?: null;
        $visibilidade = $this->sanitizeEnum((string) ($input['visibilidade'] ?? 'PRIVADO'), ['PRIVADO', 'INSTITUCIONAL', 'PUBLICO'], 'PRIVADO');

        try {
            $id = ($this->documentRepository ?? new DocumentRepository())->createAttachment([
                'conta_id' => $auth['conta_id'] ?? null,
                'orgao_id' => $auth['orgao_id'] ?? null,
                'usuario_envio_id' => $auth['usuario_id'] ?? null,
                'entidade_tipo' => $resolvedEntity['type'],
                'entidade_id' => $resolvedEntity['id'],
                'arquivo_nome' => $originalName,
                'arquivo_caminho' => $storageRelativePath,
                'arquivo_mime' => $detectedMime,
                'tamanho_bytes' => $sizeBytes,
                'hash_arquivo' => $hash,
                'visibilidade' => $visibilidade,
            ]);

            ($this->auditService ?? new AuditService())->log([
                'conta_id' => $auth['conta_id'] ?? null,
                'orgao_id' => $auth['orgao_id'] ?? null,
                'unidade_id' => $auth['unidade_id'] ?? null,
                'usuario_id' => $auth['usuario_id'] ?? null,
                'modulo_codigo' => 'DOCUMENTS',
                'acao' => 'DOCUMENT_UPLOAD',
                'resultado' => 'SUCESSO',
                'entidade_tipo' => 'anexos',
                'entidade_id' => $id,
                'detalhes' => [
                    'entidade_tipo' => $resolvedEntity['type'],
                    'entidade_id' => $resolvedEntity['id'],
                    'arquivo_nome' => $originalName,
                    'arquivo_mime' => $detectedMime,
                    'tamanho_bytes' => $sizeBytes,
                ],
                'ip_address' => $request->ipAddress(),
                'user_agent' => $request->userAgent(),
            ]);

            return ['ok' => true, 'message' => 'Documento anexado com sucesso.'];
        } catch (Throwable $exception) {
            @unlink($storageAbsolutePath);
            return ['ok' => false, 'message' => 'Falha ao registrar metadados do documento anexado.'];
        }
    }

    public function download(array $auth, int $attachmentId, Request $request): array
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        $policy = $this->policy ?? new OperationalPolicy();
        if (!$policy->canDownloadDocuments($profiles)) {
            return ['ok' => false, 'status' => 403, 'message' => 'Seu perfil nao possui permissao para baixar documentos.'];
        }

        $scopeService = $this->scopeService ?? new ScopeService();
        if (!$scopeService->hasValidContext($auth)) {
            return ['ok' => false, 'status' => 403, 'message' => 'Contexto institucional invalido para download.'];
        }

        if ($attachmentId < 1) {
            return ['ok' => false, 'status' => 422, 'message' => 'Anexo informado e invalido para download.'];
        }

        $scope = $scopeService->scopeFilter($auth);
        $repository = $this->documentRepository ?? new DocumentRepository();
        $attachment = $repository->attachmentById($scope, $attachmentId);
        if ($attachment === null) {
            $this->auditDownload($auth, $request, $attachmentId, 'NEGADO', 'anexo_nao_encontrado_ou_fora_de_escopo');
            return ['ok' => false, 'status' => 404, 'message' => 'Anexo nao encontrado no escopo institucional ativo.'];
        }

        $visibility = strtoupper((string) ($attachment['visibilidade'] ?? 'PRIVADO'));
        $ownerId = (int) ($attachment['usuario_envio_id'] ?? 0);
        $currentUserId = (int) ($auth['usuario_id'] ?? 0);
        $isOwner = $ownerId > 0 && $currentUserId > 0 && $ownerId === $currentUserId;
        $canReadOtherPrivate = $policy->canReadPrivateDocumentsFromOthers($profiles);

        if ($visibility === 'PRIVADO' && !$isOwner && !$canReadOtherPrivate) {
            $this->auditDownload($auth, $request, $attachmentId, 'NEGADO', 'arquivo_privado_sem_permissao');
            return ['ok' => false, 'status' => 403, 'message' => 'Voce nao possui permissao para baixar este anexo privado.'];
        }

        $resolvedPath = $this->safeAttachmentAbsolutePath((string) ($attachment['arquivo_caminho'] ?? ''));
        if ($resolvedPath === null || !is_file($resolvedPath) || !is_readable($resolvedPath)) {
            $this->auditDownload($auth, $request, $attachmentId, 'FALHA', 'arquivo_fisico_nao_encontrado');
            return ['ok' => false, 'status' => 404, 'message' => 'Arquivo fisico do anexo nao foi encontrado no storage.'];
        }

        $this->auditDownload($auth, $request, $attachmentId, 'SUCESSO', 'download_permitido');

        return [
            'ok' => true,
            'status' => 200,
            'file_path' => $resolvedPath,
            'download_name' => (string) ($attachment['arquivo_nome'] ?? ('anexo-' . $attachmentId)),
            'mime_type' => (string) ($attachment['arquivo_mime'] ?? 'application/octet-stream'),
        ];
    }

    private function resolveEntity(array $input): ?array
    {
        $type = $this->sanitizeEntityType($input['entidade_tipo'] ?? null);
        $id = (int) ($input['entidade_id'] ?? 0);
        if ($type !== null && $id > 0) {
            return ['type' => $type, 'id' => $id];
        }

        $entityRef = trim((string) ($input['entidade_ref'] ?? ''));
        if ($entityRef === '' || !str_contains($entityRef, ':')) {
            return null;
        }

        [$refType, $refId] = explode(':', $entityRef, 2);
        $refType = $this->sanitizeEntityType($refType);
        $refIdInt = (int) $refId;
        if ($refType === null || $refIdInt < 1) {
            return null;
        }

        return ['type' => $refType, 'id' => $refIdInt];
    }

    private function sanitizeEntityType(mixed $value): ?string
    {
        $type = trim((string) $value);
        $allowed = config('documents.allowed_entity_types', []);
        if (!is_array($allowed) || !in_array($type, $allowed, true)) {
            return null;
        }

        return $type;
    }

    private function isEntityInScope(array $scope, string $entityType, int $entityId): bool
    {
        return match ($entityType) {
            'incidentes' => ($this->incidentRepository ?? new IncidentRepository())->findIncidentById($scope, $entityId) !== null,
            'plancons' => ($this->planconRepository ?? new PlanconRepository())->findPlanconById($scope, $entityId) !== null,
            'incidentes_registros_operacionais' => ($this->documentRepository ?? new DocumentRepository())->existsIncidentRecordInScope($scope, $entityId),
            'plancon_riscos' => ($this->documentRepository ?? new DocumentRepository())->existsPlanconRiskInScope($scope, $entityId),
            default => false,
        };
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', trim($filename)) ?? '';
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return '';
        }

        return mb_substr($filename, 0, 180);
    }

    private function buildStorageRelativePath(array $auth, string $extension): string
    {
        $contaId = (int) ($auth['conta_id'] ?? 0);
        $orgaoId = (int) ($auth['orgao_id'] ?? 0);
        $now = date('Y/m');
        $random = bin2hex(random_bytes(16));
        $suffix = $extension !== '' ? '.' . $extension : '';

        return sprintf(
            'attachments/%d/%d/%s/%s%s',
            max($contaId, 0),
            max($orgaoId, 0),
            $now,
            $random,
            $suffix
        );
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function safeAttachmentAbsolutePath(string $relativePath): ?string
    {
        $normalizedRelative = trim(str_replace('\\', '/', $relativePath), '/');
        if ($normalizedRelative === '') {
            return null;
        }

        $absolute = storage_path($normalizedRelative);
        $resolved = realpath($absolute);
        $attachmentsRoot = realpath(storage_path('attachments'));
        if ($resolved === false || $attachmentsRoot === false) {
            return null;
        }

        $resolvedCheck = DIRECTORY_SEPARATOR === '\\' ? strtolower($resolved) : $resolved;
        $rootCheck = DIRECTORY_SEPARATOR === '\\' ? strtolower($attachmentsRoot) : $attachmentsRoot;
        if (!str_starts_with($resolvedCheck, $rootCheck . DIRECTORY_SEPARATOR) && $resolvedCheck !== $rootCheck) {
            return null;
        }

        return $resolved;
    }

    private function auditDownload(
        array $auth,
        Request $request,
        int $attachmentId,
        string $result,
        string $reason
    ): void {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $auth['conta_id'] ?? null,
            'orgao_id' => $auth['orgao_id'] ?? null,
            'unidade_id' => $auth['unidade_id'] ?? null,
            'usuario_id' => $auth['usuario_id'] ?? null,
            'modulo_codigo' => 'DOCUMENTS',
            'acao' => 'DOCUMENT_DOWNLOAD',
            'resultado' => $result,
            'entidade_tipo' => 'anexos',
            'entidade_id' => $attachmentId > 0 ? $attachmentId : null,
            'detalhes' => [
                'reason' => $reason,
            ],
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
