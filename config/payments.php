<?php

declare(strict_types=1);

return [
    'provider' => env('PAYMENTS_PROVIDER', 'MERCADO_PAGO'),
    'currency' => env('PAYMENTS_CURRENCY', 'BRL'),
    'checkout_token_pepper' => env('PAYMENTS_CHECKOUT_TOKEN_PEPPER', ''),
    'allow_local_fallback' => env('PAYMENTS_ALLOW_LOCAL_FALLBACK', true),
    'mercadopago' => [
        'public_key' => env('MP_PUBLIC_KEY', ''),
        'access_token' => env('MP_ACCESS_TOKEN', ''),
        'webhook_secret' => env('MP_WEBHOOK_SECRET', ''),
        'use_sandbox' => env('MP_USE_SANDBOX', true),
        'api_base_url' => env('MP_API_BASE_URL', 'https://api.mercadopago.com'),
        'webhook_url' => env('MP_WEBHOOK_URL', ''),
        'statement_descriptor' => env('MP_STATEMENT_DESCRIPTOR', 'SIGERD'),
        'binary_mode' => env('MP_BINARY_MODE', false),
        'max_installments' => env('MP_MAX_INSTALLMENTS', 12),
        'default_installments' => env('MP_DEFAULT_INSTALLMENTS', 1),
        'enable_pix' => env('MP_ENABLE_PIX', true),
        'enable_credit_card' => env('MP_ENABLE_CREDIT_CARD', true),
        'enable_debit_card' => env('MP_ENABLE_DEBIT_CARD', true),
        'excluded_payment_types' => env('MP_EXCLUDED_PAYMENT_TYPES', 'ticket,atm,prepaid_card'),
        'excluded_payment_methods' => env('MP_EXCLUDED_PAYMENT_METHODS', ''),
    ],
];
