<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Auth\SessionService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class Authenticate implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $auth = $_SESSION['auth'] ?? null;
        if (!is_array($auth) || !isset($auth['usuario_id'])) {
            return Response::redirect('/login');
        }

        if (
            $request->isMethod('POST')
            && (bool) ($auth['is_demo_trial'] ?? false)
            && $request->uri() !== '/logout'
        ) {
            Flash::set('warning', 'Conta em modo demonstrativo: operacoes de gravacao estao bloqueadas durante o trial de 3 dias.');
            return Response::redirect('/operational');
        }

        (new SessionService())->touch();

        return $next($request);
    }
}
