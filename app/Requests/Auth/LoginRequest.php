<?php

declare(strict_types=1);

namespace App\Requests\Auth;

final class LoginRequest
{
    public function validate(array $input): array
    {
        $errors = [];
        $login = trim((string) ($input['email_login'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $minLength = (int) config('auth.password_min_length', 8);

        if ($login === '') {
            $errors['email_login'] = 'Informe o login.';
        }

        if ($password === '') {
            $errors['password'] = 'Informe a senha.';
        } elseif (strlen($password) < $minLength) {
            $errors['password'] = "A senha deve ter no minimo {$minLength} caracteres.";
        }

        return $errors;
    }
}

