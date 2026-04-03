<?php

declare(strict_types=1);

$plans = $plans ?? [];
?>
<section class="hero">
    <h1>Planos SIGERD</h1>
    <p>Escolha o plano institucional mais aderente ao porte e maturidade da operacao.</p>
</section>

<?php if ($plans === []): ?>
    <section class="card">
        <h2>Catalogo ainda nao publicado</h2>
        <p>Execute o schema e o seed da Fase 1 para habilitar planos comerciais.</p>
    </section>
<?php else: ?>
    <section class="grid">
        <?php foreach ($plans as $plan): ?>
            <article class="card">
                <h2><?= e((string) $plan['nome_plano']) ?></h2>
                <p class="muted"><?= e((string) ($plan['descricao'] ?? '')) ?></p>
                <p><strong>Codigo:</strong> <?= e((string) $plan['codigo_plano']) ?></p>
                <p><strong>Mensal:</strong> R$ <?= e(number_format((float) $plan['preco_mensal'], 2, ',', '.')) ?></p>
                <p><strong>Limite de usuarios:</strong> <?= e((string) (($plan['limite_usuarios'] ?? null) !== null ? $plan['limite_usuarios'] : 'Ilimitado')) ?></p>
                <a class="button" href="<?= e(url('/demonstracao')) ?>">Quero este plano</a>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
