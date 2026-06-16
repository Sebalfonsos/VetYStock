<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);

require_once $root . '/app/helpers/functions.php';

spl_autoload_register(function (string $class) use ($root): void {
    $paths = [
        $root . '/app/core/' . $class . '.php',
        $root . '/app/models/' . $class . '.php',
        $root . '/app/services/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$route = trim((string) ($_GET['route'] ?? 'health'), '/');

header('Content-Type: application/json; charset=utf-8');

if ($route === 'health') {
    echo json_encode([
        'success' => true,
        'service' => 'catalog',
        'status' => 'ok',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

if ($route === 'products') {
    $items = (new CatalogService())->products();

    echo json_encode([
        'success' => true,
        'count' => count($items),
        'data' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Ruta no encontrada',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
