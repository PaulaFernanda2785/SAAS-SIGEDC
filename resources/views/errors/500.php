<?php

declare(strict_types=1);
?>
<section class="card">
    <h1>500 - Erro interno</h1>
    <p><?= e((string) ($message ?? 'Erro interno inesperado.')) ?></p>
    <a class="button" href="<?= e(url('/')) ?>">Ir para inicio</a>
</section>

