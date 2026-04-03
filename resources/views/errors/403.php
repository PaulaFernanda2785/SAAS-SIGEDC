<?php

declare(strict_types=1);
?>
<section class="card">
    <h1>403 - Acesso negado</h1>
    <p><?= e((string) ($message ?? 'Voce nao possui permissao para acessar este recurso.')) ?></p>
    <a class="button" href="<?= e(url('/')) ?>">Voltar para inicio</a>
</section>

