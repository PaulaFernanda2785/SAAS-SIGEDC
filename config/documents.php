<?php

declare(strict_types=1);

return [
    'max_size_mb' => 12,
    'allowed_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'text/plain',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
    ],
    'allowed_entity_types' => [
        'incidentes',
        'plancons',
        'incidentes_registros_operacionais',
        'plancon_riscos',
    ],
];
