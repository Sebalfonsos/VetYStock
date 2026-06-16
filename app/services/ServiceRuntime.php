<?php

class ServiceRuntime
{
    public static function bootstrap(array $extraPaths = []): void
    {
        require_once __DIR__ . '/../helpers/functions.php';

        spl_autoload_register(function (string $class) use ($extraPaths): void {
            $paths = [
                __DIR__ . '/../core/' . $class . '.php',
                __DIR__ . '/../models/' . $class . '.php',
                __DIR__ . '/' . $class . '.php',
                __DIR__ . '/../middleware/' . $class . '.php',
            ];

            foreach (array_merge($paths, $extraPaths) as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    return;
                }
            }
        });

        Session::start();
        date_default_timezone_set((string) config('app.timezone', 'America/Bogota'));
    }

    public static function route(): string
    {
        return trim((string) ($_GET['route'] ?? 'health'), '/');
    }

    public static function method(): string
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    }

    public static function body(): array
    {
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    public static function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    public static function bearerToken(): ?string
    {
        $header = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
