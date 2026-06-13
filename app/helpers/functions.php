<?php

function config(string $key, mixed $default = null): mixed
{
    static $config;

    $config ??= [
        'app' => require __DIR__ . '/../config/app.php',
        'database' => require __DIR__ . '/../config/database.php',
    ];

    $segments = explode('.', $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function studly(string $value): string
{
    $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value);
    return str_replace(' ', '', ucwords(strtolower(trim((string) $value))));
}

function url(string $route = ''): string
{
    $base = trim((string) config('app.base_url', ''), '/');
    $route = ltrim($route, '/');
    $query = '';
    if ($route !== '') {
        $parts = explode('&', $route, 2);
        $query = '?route=' . rawurlencode($parts[0]);
        if (isset($parts[1]) && $parts[1] !== '') {
            $query .= '&' . $parts[1];
        }
    }
    return ($base ? '/' . $base : '') . '/public/index.php' . $query;
}

function redirect(string $route): never
{
    header('Location: ' . url($route));
    exit;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, mixed $default = null): mixed
{
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $value;
}
