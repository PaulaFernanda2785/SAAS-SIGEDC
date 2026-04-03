<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\SaaS\CommercialRepository;
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
}
