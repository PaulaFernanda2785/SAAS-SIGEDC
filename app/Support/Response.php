<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    public function __construct(
        private readonly string $body = '',
        private readonly int $status = 200,
        private readonly array $headers = []
    ) {
    }

    public static function html(string $body, int $status = 200, array $headers = []): self
    {
        return new self($body, $status, $headers);
    }

    public static function view(string $view, array $data = [], string $layout = 'public', int $status = 200): self
    {
        return new self(View::render($view, $data, $layout), $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function redirect(string $path, int $status = 302): self
    {
        return new self('', $status, ['Location' => url($path)]);
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}

