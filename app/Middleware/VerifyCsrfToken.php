<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Csrf;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class VerifyCsrfToken implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        $redirectPath = $request->uri();
        if ($redirectPath === '/logout') {
            $redirectPath = '/login';
        }

        $csrfKey = (string) $request->input('_csrf_key', 'default');
        $token = (string) $request->input('_token', '');

        if ($token === '' || !Csrf::validate($token, $csrfKey)) {
            Flash::set('error', 'Token de seguranca invalido. Tente novamente.');
            return Response::redirect($redirectPath);
        }

        if (Csrf::wasProcessedRecently($token, 5)) {
            Flash::set('warning', 'Envio duplicado bloqueado. Aguarde alguns segundos.');
            return Response::redirect($redirectPath);
        }

        $response = $next($request);
        Csrf::markProcessed($token);
        Csrf::refresh($csrfKey);

        return $response;
    }
}
