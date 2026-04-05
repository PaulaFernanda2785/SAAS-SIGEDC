<?php

declare(strict_types=1);

$plans = $plans ?? [];
?>
<section class="container landing-section">
    <header class="landing-section-header">
        <span>Planos SIGERD</span>
        <h2>Escolha o plano ideal para o porte e maturidade da sua operacao</h2>
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
    <section class="container landing-section">
        <div class="landing-grid-3">
            <?php foreach ($plans as $plan): ?>
                <article class="card landing-card plan-card">
                    <h3><?= e((string) $plan['nome_plano']) ?></h3>
                    <p class="muted"><?= e((string) ($plan['descricao'] ?? '')) ?></p>
                    <p><strong>Codigo:</strong> <?= e((string) $plan['codigo_plano']) ?></p>
                    <p><strong>Mensal:</strong> R$ <?= e(number_format((float) $plan['preco_mensal'], 2, ',', '.')) ?></p>
                    <p><strong>Limite de usuarios:</strong> <?= e((string) (($plan['limite_usuarios'] ?? null) !== null ? $plan['limite_usuarios'] : 'Ilimitado')) ?></p>
                    <a class="button" href="<?= e(url('/demonstracao')) ?>">Solicitar demonstracao</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
