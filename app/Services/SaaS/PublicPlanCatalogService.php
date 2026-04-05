<?php

declare(strict_types=1);

namespace App\Services\SaaS;

use App\Repositories\SaaS\CommercialRepository;

final class PublicPlanCatalogService
{
    private const ANNUAL_DISCOUNT_PERCENT = 15.0;
    private const LAUNCH_MONTHLY_PRICE_BY_CODE = [
        'START' => 149.90,
        'PRO' => 329.90,
        'ENTERPRISE' => 649.90,
    ];

    public function __construct(private readonly ?CommercialRepository $commercialRepository = null)
    {
    }

    public function listForPublicPage(): array
    {
        $plans = ($this->commercialRepository ?? new CommercialRepository())->activePlansForPublicPage();

        return array_map(fn(array $plan): array => $this->enrichPlan($plan), $plans);
    }

    public function findSelectedOption(string $planCode, string $billingCycle): ?array
    {
        $planCode = strtoupper(trim($planCode));
        $billingCycle = strtoupper(trim($billingCycle));
        if ($planCode === '' || !in_array($billingCycle, ['MENSAL', 'ANUAL'], true)) {
            return null;
        }

        $plan = ($this->commercialRepository ?? new CommercialRepository())->planByCode($planCode);
        if ($plan === null || strtoupper((string) ($plan['status_plano'] ?? '')) !== 'ATIVO') {
            return null;
        }

        $enriched = $this->enrichPlan($plan);
        $billing = is_array($enriched['billing'] ?? null) ? $enriched['billing'] : [];

        $gross = $billingCycle === 'ANUAL'
            ? (float) ($billing['annual_gross'] ?? 0.0)
            : (float) ($billing['monthly_value'] ?? 0.0);
        $net = $billingCycle === 'ANUAL'
            ? (float) ($billing['annual_value'] ?? 0.0)
            : (float) ($billing['monthly_value'] ?? 0.0);
        $discount = max(0.0, round($gross - $net, 2));

        $enriched['selection'] = [
            'billing_cycle' => $billingCycle,
            'amount_gross' => round($gross, 2),
            'amount_net' => round($net, 2),
            'discount_value' => round($discount, 2),
            'annual_discount_percent' => (float) ($billing['annual_discount_percent'] ?? self::ANNUAL_DISCOUNT_PERCENT),
        ];

        return $enriched;
    }

    private function enrichPlan(array $plan): array
    {
        $planCode = (string) ($plan['codigo_plano'] ?? '');
        $databaseMonthlyValue = round((float) ($plan['preco_mensal'] ?? 0.0), 2);
        $launchMonthlyValue = $this->launchMonthlyPriceByCode($planCode);
        $monthlyValue = $launchMonthlyValue ?? $databaseMonthlyValue;

        $annualGross = round($monthlyValue * 12, 2);
        $annualDiscount = round($annualGross * (self::ANNUAL_DISCOUNT_PERCENT / 100), 2);
        $annualValue = round(max(0.0, $annualGross - $annualDiscount), 2);
        $annualMonthlyEquivalent = round($annualValue / 12, 2);

        $catalog = $this->catalogByCode($planCode);

        $plan['billing'] = [
            'monthly_value' => $monthlyValue,
            'monthly_original_value' => $databaseMonthlyValue,
            'launch_discount_value' => max(0.0, round($databaseMonthlyValue - $monthlyValue, 2)),
            'annual_gross' => $annualGross,
            'annual_discount_percent' => self::ANNUAL_DISCOUNT_PERCENT,
            'annual_discount_value' => $annualDiscount,
            'annual_value' => $annualValue,
            'annual_monthly_equivalent' => $annualMonthlyEquivalent,
            'is_launch_price' => $launchMonthlyValue !== null,
        ];
        $plan['catalog'] = $catalog;

        return $plan;
    }

    private function launchMonthlyPriceByCode(string $code): ?float
    {
        $normalizedCode = strtoupper(trim($code));
        if (!array_key_exists($normalizedCode, self::LAUNCH_MONTHLY_PRICE_BY_CODE)) {
            return null;
        }

        return round((float) self::LAUNCH_MONTHLY_PRICE_BY_CODE[$normalizedCode], 2);
    }

    private function catalogByCode(string $code): array
    {
        $normalizedCode = strtoupper(trim($code));

        return match ($normalizedCode) {
            'START' => [
                'label' => 'Plano de entrada institucional',
                'recommended' => false,
                'audience' => 'Orgaos em implantacao do fluxo institucional',
                'feature_groups' => [
                    'Base SaaS institucional' => [
                        'Area publica, autenticacao e painel administrativo inicial',
                        'Cadastro de contas, orgaos, unidades, usuarios e perfis',
                        'Controle de assinatura, modulos e situacao contratual',
                    ],
                    'Operacao essencial' => [
                        'Painel operacional, abertura de incidente e briefing inicial',
                        'Comando inicial, periodos operacionais e diario de registros',
                        'Relatorios operacionais basicos e exportacao inicial',
                    ],
                    'Seguranca e governanca' => [
                        'Controle por conta, orgao, unidade e perfil',
                        'Auditoria de acoes criticas',
                        'Escopo territorial por UF para cadastros e consultas',
                    ],
                ],
            ],
            'PRO' => [
                'label' => 'Plano para operacao ampliada',
                'recommended' => true,
                'audience' => 'Orgaos com operacao multiunidade e resposta mais madura',
                'feature_groups' => [
                    'Inclui tudo do Start, mais' => [
                        'PLANCON modular: territorio, riscos, cenarios, ativacao e revisoes',
                        'Gerenciamento expandido de incidentes / SCI-SCO',
                        'Staff, estrategias PAI, operacoes, planejamento e desmobilizacao',
                    ],
                    'Inteligencia operacional' => [
                        'Mapa operacional com camadas e leitura situacional',
                        'Documentos operacionais e vinculo por entidade',
                        'Relatorios administrativos e operacionais ampliados',
                    ],
                    'Governanca operacional' => [
                        'Conformidade operacional e trilhas reforcadas',
                        'Politicas de aceite legal e historico documental',
                        'Padronizacao de dados por UF e escopo institucional',
                    ],
                ],
            ],
            'ENTERPRISE' => [
                'label' => 'Plano de escala institucional e governo',
                'recommended' => false,
                'audience' => 'Contas multiinstitucionais e estruturas de alta complexidade',
                'feature_groups' => [
                    'Inclui tudo do Pro, mais' => [
                        'API controlada por chave, escopo e modulos contratados',
                        'Integracoes externas e webhooks institucionais',
                        'Automacoes, governanca ampliada e controles enterprise',
                    ],
                    'Gestao executiva e suporte' => [
                        'Analytics executivo e relatorios consolidados',
                        'Gestao refinada de SLA e suporte',
                        'Recursos avancados para multiorgao e multiunidade',
                    ],
                    'Capacidades enterprise' => [
                        'Assinatura digital institucional (quando aprovada)',
                        'Feature flags enterprise por conta/orgao/unidade',
                        'Camada pronta para ambientes de alta exigencia tecnica',
                    ],
                ],
            ],
            default => [
                'label' => 'Plano institucional',
                'recommended' => false,
                'audience' => 'Uso institucional conforme escopo contratado',
                'feature_groups' => [
                    'Recursos principais' => [
                        'Gestao SaaS institucional com controle por escopo',
                        'Operacao de incidentes e trilha de auditoria',
                        'Evolucao por modulos contratados da plataforma',
                    ],
                ],
            ],
        };
    }
}
