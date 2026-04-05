<?php

declare(strict_types=1);

$selectedPlan = (string) ($selectedPlan ?? '');
$selectedCycle = (string) ($selectedCycle ?? '');
$selection = is_array($selection ?? null) ? $selection : [];
$ufs = is_array($ufs ?? null) ? $ufs : [];
$errors = is_array($errors ?? null) ? $errors : [];

$planName = (string) ($selection['nome_plano'] ?? $selectedPlan);
$planDescription = (string) ($selection['descricao'] ?? '');
$selectionBilling = is_array($selection['selection'] ?? null) ? $selection['selection'] : [];
$billing = is_array($selection['billing'] ?? null) ? $selection['billing'] : [];

$formatMoney = static fn(float $value): string => 'R$ ' . number_format($value, 2, ',', '.');
$old = static fn(string $key, string $default = ''): string => (string) App\Support\Flash::old($key, $default);
$oldChecked = static fn(string $key): bool => App\Support\Flash::old($key) !== null;
?>
<section class="container landing-section">
    <header class="landing-section-header">
        <span>Onboarding comercial</span>
        <h2><?= e($planName) ?> no ciclo <?= e($selectedCycle) ?>: escolha como iniciar</h2>
        <div class="muted onboarding-flow-lines">
            <p>Fluxo 1: teste por 3 dias em ambiente demonstrativo, sem liberacao de gravacoes operacionais.</p>
            <p>Fluxo 2: assinatura imediata com checkout e liberacao de acesso somente apos confirmacao do pagamento.</p>
        </div>
    </header>

    <article class="card landing-card onboarding-summary-card">
        <h3>Resumo da selecao</h3>
        <?php if ($planDescription !== ''): ?>
            <p><?= e($planDescription) ?></p>
        <?php endif; ?>
        <div class="onboarding-summary-grid">
            <div><strong>Ciclo:</strong> <?= e($selectedCycle) ?></div>
            <div><strong>Valor bruto:</strong> <?= e($formatMoney((float) ($selectionBilling['amount_gross'] ?? 0.0))) ?></div>
            <div><strong>Desconto:</strong> <?= e($formatMoney((float) ($selectionBilling['discount_value'] ?? 0.0))) ?></div>
            <div><strong>Valor final:</strong> <?= e($formatMoney((float) ($selectionBilling['amount_net'] ?? 0.0))) ?></div>
        </div>
        <?php if ($selectedCycle === 'ANUAL'): ?>
            <p class="muted">
                Valor cheio anual: <?= e($formatMoney((float) ($billing['annual_gross'] ?? 0.0))) ?> |
                desconto aplicado: <?= e((string) number_format((float) ($billing['annual_discount_percent'] ?? 15.0), 0, ',', '.')) ?>%.
            </p>
        <?php endif; ?>
    </article>

    <?php if ($errors !== []): ?>
        <article class="card landing-card">
            <h3>Ajustes necessarios</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e((string) $error) ?></li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endif; ?>

    <div class="landing-grid-2 onboarding-grid">
        <article class="card landing-card onboarding-card">
            <h3>1) Testar por 3 dias</h3>
            <p class="muted">Ideal para validar navegacao e recursos em ambiente demonstrativo. Gravacoes operacionais ficam bloqueadas no backend durante o trial.</p>

            <form method="post" action="<?= e(url('/demonstracao/trial')) ?>" data-guard-submit="true" class="onboarding-form">
                <?= App\Support\Csrf::field('public_trial_start') ?>
                <input type="hidden" name="plan_code" value="<?= e($selectedPlan) ?>">
                <input type="hidden" name="billing_cycle" value="<?= e($selectedCycle) ?>">

                <div class="field">
                    <label for="trial_nome_responsavel">Nome do responsavel</label>
                    <input id="trial_nome_responsavel" name="nome_responsavel" type="text" required value="<?= e($old('nome_responsavel')) ?>">
                </div>
                <div class="field">
                    <label for="trial_email_login">Email de login</label>
                    <input id="trial_email_login" name="email_login" type="email" required value="<?= e($old('email_login')) ?>">
                </div>
                <div class="field">
                    <label for="trial_password">Senha</label>
                    <input id="trial_password" name="password" type="password" minlength="8" required>
                </div>
                <div class="field">
                    <label for="trial_password_confirmation">Confirmacao da senha</label>
                    <input id="trial_password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                </div>
                <div class="field">
                    <label for="trial_nome_conta">Instituicao/conta</label>
                    <input id="trial_nome_conta" name="nome_conta" type="text" required value="<?= e($old('nome_conta')) ?>">
                </div>
                <div class="field">
                    <label for="trial_nome_orgao">Orgao operador</label>
                    <input id="trial_nome_orgao" name="nome_orgao" type="text" required value="<?= e($old('nome_orgao')) ?>">
                </div>
                <div class="field">
                    <label for="trial_nome_unidade">Unidade</label>
                    <input id="trial_nome_unidade" name="nome_unidade" type="text" value="<?= e($old('nome_unidade', 'Unidade Sede')) ?>">
                </div>
                <div class="field">
                    <label for="trial_uf_sigla">UF</label>
                    <select id="trial_uf_sigla" name="uf_sigla" required data-uf-select>
                        <option value="">Selecione</option>
                        <?php foreach ($ufs as $uf): ?>
                            <?php $sigla = strtoupper((string) ($uf['sigla'] ?? '')); ?>
                            <option value="<?= e($sigla) ?>" <?= $old('uf_sigla') === $sigla ? 'selected' : '' ?>>
                                <?= e($sigla . ' - ' . (string) ($uf['nome'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="trial_municipio_nome">Municipio (autocomplete por UF)</label>
                    <input id="trial_municipio_nome" name="municipio_nome" type="text" list="trial_municipios" value="<?= e($old('municipio_nome')) ?>" data-municipio-input data-municipio-list="trial_municipios" autocomplete="off">
                    <datalist id="trial_municipios"></datalist>
                </div>
                <label class="checkbox-line">
                    <input type="checkbox" name="aceite_termos" value="1" <?= $oldChecked('aceite_termos') ? 'checked' : '' ?>>
                    Declaro que li os termos de uso e compreendo as regras do periodo demonstrativo.
                </label>

                <button type="submit" class="button mt-1" data-submit-label="Iniciar demonstracao de 3 dias">
                    <span class="button-text">Iniciar demonstracao de 3 dias</span>
                    <span class="button-loading" hidden>Processando...</span>
                </button>
            </form>
        </article>

        <article class="card landing-card onboarding-card">
            <h3>2) Assinar agora</h3>
            <p class="muted">Fluxo direto para cadastro e checkout do plano selecionado. O acesso e liberado somente apos confirmacao do pagamento.</p>

            <form method="post" action="<?= e(url('/demonstracao/assinar')) ?>" data-guard-submit="true" class="onboarding-form">
                <?= App\Support\Csrf::field('public_subscription_start') ?>
                <input type="hidden" name="plan_code" value="<?= e($selectedPlan) ?>">
                <input type="hidden" name="billing_cycle" value="<?= e($selectedCycle) ?>">

                <div class="field">
                    <label for="sub_nome_responsavel">Nome do responsavel</label>
                    <input id="sub_nome_responsavel" name="nome_responsavel" type="text" required value="<?= e($old('nome_responsavel')) ?>">
                </div>
                <div class="field">
                    <label for="sub_email_login">Email de login</label>
                    <input id="sub_email_login" name="email_login" type="email" required value="<?= e($old('email_login')) ?>">
                </div>
                <div class="field">
                    <label for="sub_password">Senha</label>
                    <input id="sub_password" name="password" type="password" minlength="8" required>
                </div>
                <div class="field">
                    <label for="sub_password_confirmation">Confirmacao da senha</label>
                    <input id="sub_password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                </div>
                <div class="field">
                    <label for="sub_nome_conta">Instituicao/conta</label>
                    <input id="sub_nome_conta" name="nome_conta" type="text" required value="<?= e($old('nome_conta')) ?>">
                </div>
                <div class="field">
                    <label for="sub_nome_orgao">Orgao operador</label>
                    <input id="sub_nome_orgao" name="nome_orgao" type="text" required value="<?= e($old('nome_orgao')) ?>">
                </div>
                <div class="field">
                    <label for="sub_nome_unidade">Unidade</label>
                    <input id="sub_nome_unidade" name="nome_unidade" type="text" value="<?= e($old('nome_unidade', 'Unidade Sede')) ?>">
                </div>
                <div class="field">
                    <label for="sub_uf_sigla">UF</label>
                    <select id="sub_uf_sigla" name="uf_sigla" required data-uf-select>
                        <option value="">Selecione</option>
                        <?php foreach ($ufs as $uf): ?>
                            <?php $sigla = strtoupper((string) ($uf['sigla'] ?? '')); ?>
                            <option value="<?= e($sigla) ?>" <?= $old('uf_sigla') === $sigla ? 'selected' : '' ?>>
                                <?= e($sigla . ' - ' . (string) ($uf['nome'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="sub_municipio_nome">Municipio (autocomplete por UF)</label>
                    <input id="sub_municipio_nome" name="municipio_nome" type="text" list="sub_municipios" value="<?= e($old('municipio_nome')) ?>" data-municipio-input data-municipio-list="sub_municipios" autocomplete="off">
                    <datalist id="sub_municipios"></datalist>
                </div>
                <label class="checkbox-line">
                    <input type="checkbox" name="aceite_termos" value="1" <?= $oldChecked('aceite_termos') ? 'checked' : '' ?>>
                    Confirmo aceite dos termos e autorizo o fluxo de assinatura/cobranca do plano selecionado.
                </label>

                <button type="submit" class="button mt-1" data-submit-label="Cadastrar e ir para checkout">
                    <span class="button-text">Cadastrar e ir para checkout</span>
                    <span class="button-loading" hidden>Processando...</span>
                </button>
            </form>
        </article>
    </div>
</section>
<script src="<?= e(url('/assets/js/public/onboarding.js')) ?>" defer></script>
