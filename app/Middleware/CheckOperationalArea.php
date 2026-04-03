<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

final class CheckOperationalArea implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $area = (string) ($_SESSION['auth']['area'] ?? '');
        if ($area !== 'operational') {
            return Response::view('errors/403', [
                'title' => 'Acesso negado',
                'message' => 'Seu perfil nao possui acesso a area operacional.',
            ], 'public', 403);
        }

        return $next($request);
    }
}

