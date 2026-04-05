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

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{"ok":false,"message":"Falha ao serializar resposta JSON."}';
            $status = 500;
        }

        return new self($json, $status, array_merge(['Content-Type' => 'application/json; charset=UTF-8'], $headers));
    }

    public static function redirect(string $path, int $status = 302): self
    {
        return new self('', $status, ['Location' => url($path)]);
    }

    public static function file(
        string $absolutePath,
        string $downloadName,
        ?string $mimeType = null,
        bool $inline = false
    ): self {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return new self('Arquivo nao encontrado.', 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $content = file_get_contents($absolutePath);
        if ($content === false) {
            return new self('Falha ao ler arquivo.', 500, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $mime = trim((string) $mimeType);
        if ($mime === '') {
            $mime = (string) (mime_content_type($absolutePath) ?: 'application/octet-stream');
        }

        $safeName = trim($downloadName) !== '' ? $downloadName : basename($absolutePath);
        $safeName = str_replace(['"', "\r", "\n"], ['\'', '', ''], $safeName);

        return new self($content, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($content),
            'Content-Disposition' => sprintf(
                '%s; filename="%s"',
                $inline ? 'inline' : 'attachment',
                $safeName
            ),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
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
