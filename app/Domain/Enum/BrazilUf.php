<?php

declare(strict_types=1);

namespace App\Domain\Enum;

final class BrazilUf
{
    public const ALL = [
        'AC',
        'AL',
        'AP',
        'AM',
        'BA',
        'CE',
        'DF',
        'ES',
        'GO',
        'MA',
        'MT',
        'MS',
        'MG',
        'PA',
        'PB',
        'PR',
        'PE',
        'PI',
        'RJ',
        'RN',
        'RS',
        'RO',
        'RR',
        'SC',
        'SP',
        'SE',
        'TO',
    ];

    public static function normalize(mixed $value): ?string
    {
        $uf = strtoupper(trim((string) $value));
        if (strlen($uf) !== 2 || !in_array($uf, self::ALL, true)) {
            return null;
        }

        return $uf;
    }
}
