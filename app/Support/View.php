<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'public'): string
    {
        $viewFile = resource_path('views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php');
        if (!is_file($viewFile)) {
            throw new RuntimeException("View nao encontrada: {$view}");
        }

        $content = self::renderPartial($viewFile, $data);
        $layoutFile = resource_path('views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php');
        if (!is_file($layoutFile)) {
            return $content;
        }

        $layoutData = array_merge($data, ['content' => $content]);

        return self::renderPartial($layoutFile, $layoutData);
    }

    private static function renderPartial(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}

