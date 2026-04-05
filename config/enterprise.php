<?php

declare(strict_types=1);

return [
    'api_header_name' => env('ENTERPRISE_API_HEADER_NAME', 'X-Api-Key'),
    'api_prefix_length' => (int) env('ENTERPRISE_API_PREFIX_LENGTH', 12),
    'api_token_pepper' => env('ENTERPRISE_API_TOKEN_PEPPER', ''),
];
