<?php

declare(strict_types=1);

$checkout = is_array($checkout ?? null) ? $checkout : [];
$formatMoney = static fn(float $value): string => 'R$ ' . number_format($value, 2, ',', '.');
$statusPagamento = strtoupper((string) ($checkout['status_pagamento'] ?? 'PENDENTE'));
$statusAssinatura = strtoupper((string) ($checkout['status_assinatura'] ?? 'SUSPENSA'));
$checkoutToken = (string) ($checkout['checkout_token'] ?? '');
$checkoutUrl = (string) ($checkout['checkout_url'] ?? '');
$checkoutMode = strtolower((string) ($checkout['checkout_mode'] ?? 'mercado_pago'));
?>
<section class="container landing-section">
    <header class="landing-section-header">
        <span>Checkout de assinatura</span>
        <h2>Finalize o pagamento para ativar o acesso ao SIGERD</h2>
    </header>

    <article class="card landing-card checkout-card">
        <h3>Resumo da cobranca</h3>
        <p class="muted">Meios habilitados: PIX, cartao de credito e cartao de debito.</p>
        <div class="onboarding-summary-grid">
            <div><strong>Plano:</strong> <?= e((string) ($checkout['plano_nome'] ?? '')) ?> (<?= e((string) ($checkout['plano_codigo'] ?? '')) ?>)</div>
            <div><strong>Ciclo:</strong> <?= e((string) ($checkout['ciclo_cobranca'] ?? '')) ?></div>
            <div><strong>Valor bruto:</strong> <?= e($formatMoney((float) ($checkout['valor_bruto'] ?? 0.0))) ?></div>
            <div><strong>Desconto:</strong> <?= e($formatMoney((float) ($checkout['desconto_valor'] ?? 0.0))) ?></div>
            <div><strong>Valor final:</strong> <?= e($formatMoney((float) ($checkout['valor_liquido'] ?? 0.0))) ?></div>
            <div><strong>Vencimento:</strong> <?= e((string) ($checkout['vence_em'] ?? '')) ?></div>
            <div><strong>Status pagamento:</strong> <?= e($statusPagamento) ?></div>
            <div><strong>Status assinatura:</strong> <?= e($statusAssinatura) ?></div>
        </div>

        <?php if ($statusPagamento === 'APROVADO'): ?>
            <p class="muted">Pagamento ja aprovado. O acesso pode ser realizado pelo login institucional.</p>
            <a class="button" href="<?= e(url('/acessar-plataforma')) ?>">Acessar plataforma</a>
        <?php else: ?>
            <?php if ($checkoutUrl !== ''): ?>
                <p class="muted">Se o checkout externo estiver disponivel, conclua por ele e depois retorne para confirmar.</p>
                <a class="button button-secondary" href="<?= e($checkoutUrl) ?>" target="_blank" rel="noopener">Abrir checkout Mercado Pago</a>
            <?php endif; ?>
            <?php if ($checkoutMode === 'fallback_local'): ?>
                <form method="post" action="<?= e(url('/checkout/confirmar')) ?>" data-guard-submit="true" class="mt-1">
                    <?= App\Support\Csrf::field('public_checkout_confirm') ?>
                    <input type="hidden" name="checkout_token" value="<?= e($checkoutToken) ?>">
                    <button type="submit" class="button" data-submit-label="Confirmar pagamento e ativar assinatura">
                        <span class="button-text">Confirmar pagamento e ativar assinatura</span>
                        <span class="button-loading" hidden>Processando...</span>
                    </button>
                </form>
            <?php else: ?>
                <p class="muted mt-1">A ativacao ocorre automaticamente apos confirmacao de pagamento pelo Mercado Pago (retorno/webhook).</p>
            <?php endif; ?>
        <?php endif; ?>
    </article>
</section>
