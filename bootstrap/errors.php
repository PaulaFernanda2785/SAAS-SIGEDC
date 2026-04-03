<?php

declare(strict_types=1);

use App\Support\Logger;
use App\Support\Response;

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception): void {
    Logger::error('app', 'Excecao nao tratada', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);

    $debug = (bool) config('app.debug', false);
    $message = $debug ? $exception->getMessage() : 'Ocorreu um erro interno.';

    Response::view('errors/500', [
        'title' => 'Erro interno',
        'message' => $message,
    ], 'public', 500)->send();
});

