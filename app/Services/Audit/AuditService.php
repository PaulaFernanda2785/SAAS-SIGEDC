<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Repositories\Audit\AuditLogRepository;
use App\Support\Logger;
use Throwable;

final class AuditService
{
    public function __construct(private readonly ?AuditLogRepository $repository = null)
    {
    }

    public function log(array $payload): void
    {
        if (!(bool) config('audit.enabled', true)) {
            return;
        }

        try {
            ($this->repository ?? new AuditLogRepository())->record($payload);
        } catch (Throwable $exception) {
            Logger::error('audit', 'Falha ao registrar auditoria', [
                'error' => $exception->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}

