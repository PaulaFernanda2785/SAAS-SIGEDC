<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use App\Services\Auth\PasswordResetService;
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

    public function showForgotPassword(Request $request): Response
    {
        return Response::view('auth/forgot-password', [
            'title' => 'Recuperacao de senha',
            'errors' => Flash::get('errors', []),
        ], 'auth');
    }

    public function forgotPassword(Request $request): Response
    {
        $emailLogin = trim(strtolower((string) $request->input('email_login', '')));
        Flash::setOldInput(['email_login' => $emailLogin]);
        if ($emailLogin === '') {
            Flash::set('error', 'Informe o login para recuperar a senha.');
            return Response::redirect('/forgot-password');
        }

        $result = (new PasswordResetService())->requestToken($emailLogin, $request);
        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao solicitar recuperacao.'));
            return Response::redirect('/forgot-password');
        }

        Flash::set('success', (string) ($result['message'] ?? 'Solicitacao recebida.'));
        if (isset($result['token'])) {
            $token = (string) $result['token'];
            $expiresAt = (string) ($result['token_expires_at'] ?? '');
            Flash::set('warning', "Token de teste (ambiente local): {$token} | expira em: {$expiresAt}");
        }

        return Response::redirect('/forgot-password');
    }

    public function showResetPassword(Request $request): Response
    {
        return Response::view('auth/reset-password', [
            'title' => 'Redefinir senha',
            'errors' => Flash::get('errors', []),
            'token' => (string) $request->input('token', ''),
        ], 'auth');
    }

    public function resetPassword(Request $request): Response
    {
        $token = trim((string) $request->input('token', ''));
        $password = (string) $request->input('password', '');
        $passwordConfirmation = (string) $request->input('password_confirmation', '');
        $minLength = (int) config('auth.password_min_length', 8);

        if ($token === '' || $password === '' || $passwordConfirmation === '') {
            Flash::set('error', 'Preencha token, nova senha e confirmacao.');
            return Response::redirect('/reset-password');
        }

        if (strlen($password) < $minLength) {
            Flash::set('error', "A senha deve ter no minimo {$minLength} caracteres.");
            return Response::redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirmation) {
            Flash::set('error', 'A confirmacao da senha nao confere.');
            return Response::redirect('/reset-password?token=' . urlencode($token));
        }

        $result = (new PasswordResetService())->resetPassword($token, $password);
        if (!($result['ok'] ?? false)) {
            Flash::set('error', (string) ($result['message'] ?? 'Falha ao redefinir senha.'));
            return Response::redirect('/reset-password?token=' . urlencode($token));
        }

        Flash::set('success', (string) ($result['message'] ?? 'Senha redefinida com sucesso.'));
        return Response::redirect('/login');
    }
}
