<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\SaaS\CommercialRepository;
use App\Support\Request;
use App\Support\Response;

final class DashboardController
{
    public function __construct(private readonly ?CommercialRepository $commercialRepository = null)
    {
    }

    public function index(Request $request): Response
    {
        $summary = ($this->commercialRepository ?? new CommercialRepository())->summary();

        return Response::view('admin/dashboard', [
            'title' => 'Painel Administrativo SaaS',
            'auth' => $_SESSION['auth'] ?? [],
            'summary' => $summary,
        ], 'admin');
    }
}
