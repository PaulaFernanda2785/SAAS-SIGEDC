<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\SaaS\CommercialRepository;
use App\Support\Request;
use App\Support\Response;
use Throwable;

final class HomeController
{
    public function __construct(private readonly ?CommercialRepository $commercialRepository = null)
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
            $plans = ($this->commercialRepository ?? new CommercialRepository())->activePlansForPublicPage();
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
        return Response::view('public/demo', [
            'title' => 'Solicitar Demonstracao',
        ], 'public');
    }
}
