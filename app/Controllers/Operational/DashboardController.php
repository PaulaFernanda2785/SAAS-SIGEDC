<?php

declare(strict_types=1);

namespace App\Controllers\Operational;

use App\Support\Request;
use App\Support\Response;

final class DashboardController
{
    public function index(Request $request): Response
    {
        return Response::view('operational/dashboard', [
            'title' => 'Painel Operacional Institucional',
            'auth' => $_SESSION['auth'] ?? [],
        ], 'operational');
    }
}

