<?php

declare(strict_types=1);

namespace App\Domain\Enum;

final class InstitutionScope
{
    public const PROPRIA_UNIDADE = 'PROPRIA_UNIDADE';
    public const PROPRIO_ORGAO = 'PROPRIO_ORGAO';
    public const MUNICIPAL = 'MUNICIPAL';
    public const REGIONAL = 'REGIONAL';
    public const ESTADUAL = 'ESTADUAL';
    public const MULTIINSTITUCIONAL = 'MULTIINSTITUCIONAL';
    public const GLOBAL = 'GLOBAL';

    public static function all(): array
    {
        return [
            self::PROPRIA_UNIDADE,
            self::PROPRIO_ORGAO,
            self::MUNICIPAL,
            self::REGIONAL,
            self::ESTADUAL,
            self::MULTIINSTITUCIONAL,
            self::GLOBAL,
        ];
    }

    public static function restrictivePriority(): array
    {
        return [
            self::PROPRIA_UNIDADE,
            self::PROPRIO_ORGAO,
            self::MUNICIPAL,
            self::REGIONAL,
            self::ESTADUAL,
            self::MULTIINSTITUCIONAL,
            self::GLOBAL,
        ];
    }
}
