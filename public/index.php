<?php

declare(strict_types=1);

/**
 * Fallback for environments where Apache rewrites every request to index.php.
 * If the requested path is an existing file under public/, serve it directly.
 */
$uriPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$uriPath = '/' . ltrim($uriPath, '/');

if ($uriPath !== '/') {
    $relativePath = ltrim($uriPath, '/');
    $candidate = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    $realCandidate = realpath($candidate);
    $realPublic = realpath(__DIR__);

    if (
        $realCandidate !== false
        && $realPublic !== false
        && is_file($realCandidate)
        && str_starts_with($realCandidate, $realPublic . DIRECTORY_SEPARATOR)
    ) {
        $extension = strtolower((string) pathinfo($realCandidate, PATHINFO_EXTENSION));
        $mimeByExtension = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'txt' => 'text/plain; charset=UTF-8',
            'map' => 'application/json; charset=UTF-8',
        ];

        $contentType = $mimeByExtension[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . (string) filesize($realCandidate));

        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'HEAD') {
            readfile($realCandidate);
        }

        exit;
    }
}

$app = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$app->run();
