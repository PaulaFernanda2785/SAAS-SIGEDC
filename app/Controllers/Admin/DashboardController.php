<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Support\Request;
use App\Support\Response;

final class DashboardController
{
    public function index(Request $request): Response
    {
        return Response::view('admin/dashboard', [
            'title' => 'Painel Administrativo SaaS',
            'auth' => $_SESSION['auth'] ?? [],
        ], 'admin');
    }
}

