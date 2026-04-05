<?php

declare(strict_types=1);

$plans = $plans ?? [];
$formatMoney = static fn(float $value): string => 'R$ ' . number_format($value, 2, ',', '.');
?>
<section class="container landing-section">
    <header class="landing-section-header">
        <span>Planos SIGERD de estreia</span>
        <h2>Escolha o plano ideal para sua instituicao, no ciclo mensal ou anual</h2>
        <p class="muted">No ciclo anual, o valor total recebe 15% de desconto sobre 12 mensalidades, reduzindo o custo medio por mes.</p>
    </header>
</section>

<?php if ($plans === []): ?>
    <section class="container landing-section">
        <article class="card landing-card">
            <h3>Catalogo ainda nao publicado</h3>
            <p>Execute o schema e o seed da Fase 1 para habilitar os planos comerciais do SIGERD.</p>
        </article>
    </section>
<?php else: ?>
    <section class="container container-wide landing-section">
        <div class="landing-grid-3 plans-grid">
            <?php foreach ($plans as $plan): ?>
                <?php
                $catalog = is_array($plan['catalog'] ?? null) ? $plan['catalog'] : [];
                $billing = is_array($plan['billing'] ?? null) ? $plan['billing'] : [];
                $featureGroups = is_array($catalog['feature_groups'] ?? null) ? $catalog['feature_groups'] : [];
                $planCode = (string) ($plan['codigo_plano'] ?? '');
                $monthlyValue = (float) ($billing['monthly_value'] ?? 0.0);
                $monthlyOriginalValue = (float) ($billing['monthly_original_value'] ?? $monthlyValue);
                $annualValue = (float) ($billing['annual_value'] ?? 0.0);
                $annualGross = (float) ($billing['annual_gross'] ?? 0.0);
                $annualDiscountValue = (float) ($billing['annual_discount_value'] ?? 0.0);
                $launchDiscountValue = (float) ($billing['launch_discount_value'] ?? 0.0);
                $annualMonthlyEquivalent = (float) ($billing['annual_monthly_equivalent'] ?? 0.0);
                $annualDiscountPercent = (float) ($billing['annual_discount_percent'] ?? 15.0);
                $isLaunchPrice = (bool) ($billing['is_launch_price'] ?? false);
                $isRecommended = (bool) ($catalog['recommended'] ?? false);
                $monthlyCtaLink = url('/demonstracao?plano=' . rawurlencode($planCode) . '&ciclo=MENSAL');
                $annualCtaLink = url('/demonstracao?plano=' . rawurlencode($planCode) . '&ciclo=ANUAL');
                ?>
                <article class="card landing-card plan-card<?= $isRecommended ? ' plan-card--recommended' : '' ?>">
                    <div class="plan-card-head">
                        <div>
                            <h3><?= e((string) $plan['nome_plano']) ?></h3>
                            <p class="plan-card-subtitle"><?= e((string) ($catalog['label'] ?? 'Plano institucional')) ?></p>
                        </div>
                        <?php if ($isRecommended): ?>
                            <span class="plan-card-badge">Mais recomendado</span>
                        <?php endif; ?>
                    </div>

                    <p class="muted"><?= e((string) ($plan['descricao'] ?? '')) ?></p>
                    <p><strong>Indicado para:</strong> <?= e((string) ($catalog['audience'] ?? 'Uso institucional')) ?></p>
                    <p><strong>Plano:</strong> <?= e($planCode) ?></p>
                    <p><strong>Usuarios incluidos:</strong> <?= e((string) (($plan['limite_usuarios'] ?? null) !== null ? $plan['limite_usuarios'] : 'Ilimitado')) ?></p>

                    <div class="plan-pricing">
                        <?php if ($isLaunchPrice && $launchDiscountValue > 0): ?>
                            <p class="plan-launch-note">
                                Oferta de lancamento: de <?= e($formatMoney($monthlyOriginalValue)) ?>/mes por
                                <?= e($formatMoney($monthlyValue)) ?>/mes.
                            </p>
                        <?php endif; ?>
                        <div class="plan-pricing-line">
                            <span>Ciclo mensal (renovacao mes a mes)</span>
                            <strong><?= e($formatMoney($monthlyValue)) ?>/mes</strong>
                        </div>
                        <div class="plan-pricing-line plan-pricing-line--annual">
                            <span>Ciclo anual (<?= e(number_format($annualDiscountPercent, 0, ',', '.')) ?>% de desconto)</span>
                            <strong><?= e($formatMoney($annualValue)) ?>/ano</strong>
                        </div>
                        <p class="plan-pricing-caption">
                            Valor anual sem desconto: <?= e($formatMoney($annualGross)) ?>.<br>
                            Economia no anual: <?= e($formatMoney($annualDiscountValue)) ?>.<br>
                            Equivalente mensal no anual: <?= e($formatMoney($annualMonthlyEquivalent)) ?>/mes.
                        </p>
                    </div>

                    <div class="plan-cycle-actions">
                        <a class="button plan-cycle-button" href="<?= e($monthlyCtaLink) ?>">Quero este plano mensal</a>
                        <a class="button button-secondary plan-cycle-button" href="<?= e($annualCtaLink) ?>">Quero este plano anual</a>
                    </div>

                    <?php if ($featureGroups !== []): ?>
                        <div class="plan-feature-groups">
                            <?php foreach ($featureGroups as $groupTitle => $items): ?>
                                <?php $features = is_array($items) ? $items : []; ?>
                                <section class="plan-feature-group">
                                    <h4><?= e((string) $groupTitle) ?></h4>
                                    <?php if ($features !== []): ?>
                                        <ul>
                                            <?php foreach ($features as $feature): ?>
                                                <li><?= e((string) $feature) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </section>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
