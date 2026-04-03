<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;

final class AuthController
{
    public function showLogin(Request $request): Response
    {
        return Response::view('auth/login', [
            'title' => 'Acesso ao SIGERD',
            'errors' => Flash::get('errors', []),
        ], 'auth');
    }

    public function login(Request $request): Response
    {
        $validator = new LoginRequest();
        $errors = $validator->validate($request->all());
        if ($errors !== []) {
            Flash::set('errors', $errors);
            Flash::setOldInput(['email_login' => (string) $request->input('email_login', '')]);
            return Response::redirect('/login');
        }

        $result = (new AuthService())->attempt(
            trim((string) $request->input('email_login')),
            (string) $request->input('password'),
            $request
        );

        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Falha de autenticacao.'));
            Flash::setOldInput(['email_login' => (string) $request->input('email_login', '')]);
            return Response::redirect('/login');
        }

        return Response::redirect((string) $result['redirect']);
    }

    public function logout(Request $request): Response
    {
        (new AuthService())->logout($request);
        Flash::set('success', 'Sessao encerrada com sucesso.');

        return Response::redirect('/login');
    }
}

