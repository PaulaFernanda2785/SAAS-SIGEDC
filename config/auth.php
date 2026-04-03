<?php

declare(strict_types=1);

return [
    'login_field' => env('AUTH_LOGIN_FIELD', 'email_login'),
    'password_min_length' => (int) env('AUTH_PASSWORD_MIN_LENGTH', 8),
    'max_attempts' => (int) env('AUTH_MAX_ATTEMPTS', 5),
];

