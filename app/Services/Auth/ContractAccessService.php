<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\SaaS\CommercialRepository;
use DateTimeImmutable;
use Throwable;

final class ContractAccessService
{
    public function __construct(private readonly ?CommercialRepository $commercialRepository = null)
    {
    }

    public function evaluate(int $contaId): array
    {
        try {
            $commercialRepository = $this->commercialRepository ?? new CommercialRepository();
            $contract = $commercialRepository->contractForAuth($contaId);

            if ($contract === null) {
                $latestAssinatura = $commercialRepository->latestAssinaturaByConta($contaId);
                if ($latestAssinatura !== null) {
                    $latestStatus = strtoupper((string) ($latestAssinatura['status_assinatura'] ?? ''));
                    $latestReason = strtoupper((string) ($latestAssinatura['motivo_status'] ?? ''));

                    if (
                        $latestStatus === 'TRIAL'
                        && str_contains($latestReason, 'TRIAL_DEMO_PUBLICO_3_DIAS')
                        && $this->isExpiredDate((string) ($latestAssinatura['expira_em'] ?? ''))
                    ) {
                        $expiresAt = (string) ($latestAssinatura['expira_em'] ?? '');
                        return [
                            'ok' => false,
                            'reason' => 'trial_demo_expirado',
                            'message' => 'Periodo de demonstracao encerrado em ' . ($expiresAt !== '' ? $expiresAt : 'data indisponivel') . '. Para continuar, escolha um plano em /planos e conclua a assinatura/pagamento.',
                        ];
                    }

                    if (
                        $latestStatus === 'SUSPENSA'
                        && str_contains($latestReason, 'AGUARDANDO_PAGAMENTO_PORTAL_PUBLICO')
                    ) {
                        return [
                            'ok' => false,
                            'reason' => 'assinatura_aguardando_pagamento',
                            'message' => 'Cadastro encontrado com assinatura pendente de pagamento. Finalize o checkout para liberar o acesso.',
                        ];
                    }
                }

                return [
                    'ok' => false,
                    'reason' => 'assinatura_ausente_ou_inativa',
                    'message' => 'Conta sem assinatura ativa para acesso.',
                ];
            }

            if ((int) ($contract['auth_liberado'] ?? 0) < 1) {
                return [
                    'ok' => false,
                    'reason' => 'modulo_auth_nao_liberado',
                    'message' => 'Modulo AUTH nao liberado para a assinatura atual.',
                ];
            }

            $assinaturaId = (int) $contract['id'];
            return [
                'ok' => true,
                'reason' => null,
                'message' => null,
                'assinatura' => $contract,
                'modulos_liberados' => $commercialRepository->moduleCodesByAssinatura($assinaturaId),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'reason' => 'contrato_indisponivel',
                'message' => 'Nao foi possivel validar a situacao contratual.',
            ];
        }
    }

    private function isExpiredDate(string $date): bool
    {
        $date = trim($date);
        if ($date === '') {
            return false;
        }

        try {
            $target = new DateTimeImmutable($date);
            $today = new DateTimeImmutable('today');
            return $target < $today;
        } catch (Throwable) {
            return false;
        }
    }
}
