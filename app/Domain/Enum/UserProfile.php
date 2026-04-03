<?php

declare(strict_types=1);

namespace App\Domain\Enum;

final class UserProfile
{
    public const ADMIN_MASTER = 'ADMIN_MASTER';
    public const ADMIN_ORGAO = 'ADMIN_ORGAO';
    public const GESTOR = 'GESTOR';
    public const COORDENADOR = 'COORDENADOR';
    public const ANALISTA = 'ANALISTA';
    public const OPERADOR = 'OPERADOR';
    public const LEITOR = 'LEITOR';
    public const FINANCEIRO = 'FINANCEIRO';
    public const SUPORTE = 'SUPORTE';
    public const CONVIDADO = 'CONVIDADO';

    public static function adminProfiles(): array
    {
        return [
            self::ADMIN_MASTER,
            self::ADMIN_ORGAO,
            self::FINANCEIRO,
            self::SUPORTE,
        ];
    }
}

