<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\SaaS\CommercialRepository;
use App\Services\SaaS\PublicPlanCatalogService;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class HomeController
{
    public function __construct(
        private readonly ?CommercialRepository $commercialRepository = null,
        private readonly ?PublicPlanCatalogService $publicPlanCatalogService = null,
    )
    {
    }

    public function index(Request $request): Response
    {
        return Response::view('public/home', [
            'title' => 'SIGERD - Plataforma SaaS Institucional',
        ], 'public');
    }

    public function plans(Request $request): Response
    {
        $plans = [];
        try {
            $plans = ($this->publicPlanCatalogService ?? new PublicPlanCatalogService(
                $this->commercialRepository ?? new CommercialRepository()
            ))->listForPublicPage();
        } catch (Throwable) {
            $plans = [];
        }

        return Response::view('public/plans', [
            'title' => 'Planos do SIGERD',
            'plans' => $plans,
        ], 'public');
    }

    public function demo(Request $request): Response
    {
        $selectedPlan = strtoupper(trim((string) $request->input('plano', '')));
        if (!preg_match('/^[A-Z0-9_-]{2,30}$/', $selectedPlan)) {
            $selectedPlan = '';
        }

        $selectedCycle = strtoupper(trim((string) $request->input('ciclo', '')));
        if (!in_array($selectedCycle, ['MENSAL', 'ANUAL'], true)) {
            $selectedCycle = '';
        }

        if (!in_array($selectedPlan, ['START', 'PRO', 'ENTERPRISE'], true) || $selectedCycle === '') {
            return Response::redirect('/planos');
        }

        return Response::view('public/demo', [
            'title' => 'Solicitar Demonstracao',
            'selectedPlan' => $selectedPlan,
            'selectedCycle' => $selectedCycle,
        ], 'public');
    }
}
