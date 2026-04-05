<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Support\Logger;
use Throwable;

final class MercadoPagoGatewayService
{
    public function createCheckoutPreference(array $checkout): array
    {
        $externalReference = (string) ($checkout['external_reference'] ?? '');
        $fallbackUrl = url('/checkout?token=' . rawurlencode($externalReference));
        $allowLocalFallback = (bool) config('payments.allow_local_fallback', true);

        $accessToken = trim((string) config('payments.mercadopago.access_token', ''));
        if ($accessToken === '') {
            if (!$allowLocalFallback) {
                return [
                    'ok' => false,
                    'mode' => 'error',
                    'message' => 'Token Mercado Pago nao configurado. Defina MP_ACCESS_TOKEN para habilitar checkout real.',
                    'gateway_reference' => null,
                    'checkout_url' => '',
                    'payload' => null,
                ];
            }

            return $this->fallbackLocalResponse(
                'Token Mercado Pago nao configurado. Checkout local habilitado para fluxo demonstrativo.',
                $fallbackUrl
            );
        }

        if (!function_exists('curl_init')) {
            if (!$allowLocalFallback) {
                return [
                    'ok' => false,
                    'mode' => 'error',
                    'message' => 'Extensao cURL indisponivel para comunicacao com Mercado Pago.',
                    'gateway_reference' => null,
                    'checkout_url' => '',
                    'payload' => null,
                ];
            }

            return $this->fallbackLocalResponse(
                'Extensao cURL indisponivel. Checkout local habilitado.',
                $fallbackUrl
            );
        }

        try {
            $baseUrl = rtrim((string) config('payments.mercadopago.api_base_url', 'https://api.mercadopago.com'), '/');
            $endpoint = $baseUrl . '/checkout/preferences';
            $useSandbox = (bool) config('payments.mercadopago.use_sandbox', true);
            $binaryMode = (bool) config('payments.mercadopago.binary_mode', false);
            $notificationUrl = trim((string) ($checkout['notification_url'] ?? config('payments.mercadopago.webhook_url', '')));
            $statementDescriptor = strtoupper(trim((string) config('payments.mercadopago.statement_descriptor', 'SIGERD')));
            if ($statementDescriptor !== '') {
                $statementDescriptor = substr(preg_replace('/[^A-Z0-9 ]/', '', $statementDescriptor) ?? 'SIGERD', 0, 13);
            }
            $paymentMethods = $this->paymentMethodsPreferences();
            $payerName = trim((string) ($checkout['payer_name'] ?? ''));
            $payerEmail = trim((string) ($checkout['payer_email'] ?? ''));

            $payload = [
                'external_reference' => $externalReference,
                'statement_descriptor' => $statementDescriptor !== '' ? $statementDescriptor : null,
                'items' => [[
                    'id' => (string) ($checkout['item_id'] ?? 'SIGERD_PLAN'),
                    'title' => (string) ($checkout['item_title'] ?? 'Assinatura SIGERD'),
                    'description' => (string) ($checkout['item_description'] ?? 'Assinatura da plataforma SIGERD'),
                    'quantity' => max(1, (int) ($checkout['item_quantity'] ?? 1)),
                    'currency_id' => (string) ($checkout['currency'] ?? config('payments.currency', 'BRL')),
                    'unit_price' => round((float) ($checkout['unit_price'] ?? 0.0), 2),
                ]],
                'payer' => [
                    'name' => $payerName,
                    'email' => $payerEmail,
                ],
                'back_urls' => [
                    'success' => (string) ($checkout['success_url'] ?? $fallbackUrl),
                    'failure' => (string) ($checkout['failure_url'] ?? $fallbackUrl),
                    'pending' => (string) ($checkout['pending_url'] ?? $fallbackUrl),
                ],
                'auto_return' => 'approved',
                'binary_mode' => $binaryMode,
                'payment_methods' => $paymentMethods,
                'metadata' => [
                    'sistema' => 'SIGERD',
                    'conta_id' => (int) ($checkout['conta_id'] ?? 0),
                    'assinatura_id' => (int) ($checkout['assinatura_id'] ?? 0),
                    'fatura_id' => (int) ($checkout['fatura_id'] ?? 0),
                ],
            ];

            if ($notificationUrl !== '') {
                $payload['notification_url'] = $notificationUrl;
            }

            $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($payloadJson === false) {
                throw new \RuntimeException('Falha ao serializar payload de checkout Mercado Pago.');
            }

            $curl = curl_init($endpoint);
            if ($curl === false) {
                throw new \RuntimeException('Nao foi possivel inicializar requisicao cURL para Mercado Pago.');
            }

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payloadJson,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'X-Idempotency-Key: ' . hash('sha256', $externalReference),
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);

            $responseRaw = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            $responseData = is_string($responseRaw) ? json_decode($responseRaw, true) : null;

            if ($curlError !== '') {
                throw new \RuntimeException('Erro cURL Mercado Pago: ' . $curlError);
            }

            if ($httpCode < 200 || $httpCode >= 300 || !is_array($responseData)) {
                $excerpt = is_string($responseRaw) ? substr($responseRaw, 0, 300) : '';
                throw new \RuntimeException('Resposta invalida Mercado Pago (' . $httpCode . '). ' . $excerpt);
            }

            $checkoutUrl = $useSandbox
                ? (string) ($responseData['sandbox_init_point'] ?? '')
                : (string) ($responseData['init_point'] ?? '');
            if ($checkoutUrl === '') {
                $checkoutUrl = (string) ($responseData['init_point'] ?? '');
            }

            return [
                'ok' => true,
                'mode' => 'mercado_pago',
                'message' => 'Checkout Mercado Pago criado com sucesso.',
                'gateway_reference' => (string) ($responseData['id'] ?? ''),
                'checkout_url' => $checkoutUrl !== '' ? $checkoutUrl : $fallbackUrl,
                'payload' => [
                    'id' => $responseData['id'] ?? null,
                    'init_point' => $responseData['init_point'] ?? null,
                    'sandbox_init_point' => $responseData['sandbox_init_point'] ?? null,
                ],
            ];
        } catch (Throwable $exception) {
            Logger::warning('payments', 'Falha ao gerar checkout Mercado Pago. Fallback local acionado.', [
                'error' => $exception->getMessage(),
                'external_reference_prefix' => substr($externalReference, 0, 12),
            ]);

            if (!$allowLocalFallback) {
                return [
                    'ok' => false,
                    'mode' => 'error',
                    'message' => 'Checkout Mercado Pago indisponivel: ' . $exception->getMessage(),
                    'gateway_reference' => null,
                    'checkout_url' => '',
                    'payload' => null,
                ];
            }

            return $this->fallbackLocalResponse(
                'Checkout Mercado Pago indisponivel no momento. Fluxo local de confirmacao habilitado.',
                $fallbackUrl
            );
        }
    }

    private function paymentMethodsPreferences(): array
    {
        $excludedTypes = $this->csvList((string) config('payments.mercadopago.excluded_payment_types', ''));
        $excludedMethods = $this->csvList((string) config('payments.mercadopago.excluded_payment_methods', ''));

        if (!(bool) config('payments.mercadopago.enable_pix', true)) {
            $excludedTypes[] = 'bank_transfer';
        }
        if (!(bool) config('payments.mercadopago.enable_credit_card', true)) {
            $excludedTypes[] = 'credit_card';
        }
        if (!(bool) config('payments.mercadopago.enable_debit_card', true)) {
            $excludedTypes[] = 'debit_card';
        }

        $excludedTypeEntries = [];
        foreach (array_values(array_unique($excludedTypes)) as $id) {
            if ($id === '') {
                continue;
            }
            $excludedTypeEntries[] = ['id' => $id];
        }

        $excludedMethodEntries = [];
        foreach (array_values(array_unique($excludedMethods)) as $id) {
            if ($id === '') {
                continue;
            }
            $excludedMethodEntries[] = ['id' => $id];
        }

        $maxInstallments = (int) config('payments.mercadopago.max_installments', 12);
        if ($maxInstallments < 1) {
            $maxInstallments = 12;
        }

        $defaultInstallments = (int) config('payments.mercadopago.default_installments', 1);
        if ($defaultInstallments < 1 || $defaultInstallments > $maxInstallments) {
            $defaultInstallments = 1;
        }

        return [
            'installments' => $maxInstallments,
            'default_installments' => $defaultInstallments,
            'excluded_payment_types' => $excludedTypeEntries,
            'excluded_payment_methods' => $excludedMethodEntries,
        ];
    }

    private function csvList(string $value): array
    {
        $parts = explode(',', $value);
        $items = [];
        foreach ($parts as $part) {
            $item = strtolower(trim($part));
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function fallbackLocalResponse(string $message, string $fallbackUrl): array
    {
        return [
            'ok' => false,
            'mode' => 'fallback_local',
            'message' => $message,
            'gateway_reference' => null,
            'checkout_url' => $fallbackUrl,
            'payload' => null,
        ];
    }
}
