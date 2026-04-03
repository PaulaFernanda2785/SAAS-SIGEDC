<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Support\Request;
use App\Support\Response;

final class HomeController
{
    public function index(Request $request): Response
    {
        return Response::view('public/home', [
            'title' => 'SIGERD - Fundacao Tecnica',
        ], 'public');
    }
}

