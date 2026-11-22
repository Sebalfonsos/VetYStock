<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new CartService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'cart', 'status' => 'ok']);
}

// Los visitantes también compran: la app principal firma sus llamadas con un token de sistema.
$claims = ServiceRuntime::requireClaims();
$token = (string) ServiceRuntime::bearerToken();
$userId = (int) ($claims['sub'] ?? 0) ?: null;
$service->ensureSchema();

$parts = explode('/', $route);
$resource = $parts[0];

if ($resource === 'cart') {
    $key = $parts[1] ?? '';
    // La llave del carrito la genera la app principal; se exige el formato para no aceptar cualquier texto.
    if (!preg_match('/^[a-f0-9]{32}$/', $key)) {
        ServiceRuntime::json(['success' => false, 'message' => 'Carrito inválido.'], 400);
    }
    $action = $parts[2] ?? '';
    $productId = (int) ($parts[3] ?? 0);

    $result = match (true) {
        $method === 'GET' && $action === '' => $service->cart($key),
        $method === 'DELETE' && $action === '' => $service->clear($key),
        $method === 'POST' && $action === 'items' => $service->putItem($key, ServiceRuntime::body(), 'add'),
        $method === 'PUT' && $action === 'items' => $service->putItem($key, ServiceRuntime::body(), 'set'),
        $method === 'DELETE' && $action === 'items' && $productId > 0 => $service->removeItem($key, $productId),
        $method === 'POST' && $action === 'checkout' => $service->checkout($key, ServiceRuntime::body(), $token, $userId),
        default => null,
    };

    if ($result !== null) {
        ServiceRuntime::json($result, ($result['success'] ?? false) ? 200 : 422);
    }
}

if ($resource === 'orders') {
    // Ver y cerrar pedidos es trabajo del personal, no de visitantes.
    if ($userId === null) {
        ServiceRuntime::json(['success' => false, 'message' => 'Debes iniciar sesión.'], 403);
    }

    if ($method === 'GET' && count($parts) === 1) {
        ServiceRuntime::json($service->orders($_GET));
    }

    $orderId = (int) ($parts[1] ?? 0);
    if ($method === 'POST' && $orderId > 0 && ($parts[2] ?? '') === 'status') {
        $result = $service->setStatus($orderId, (string) (ServiceRuntime::body()['status'] ?? ''), $token);
        ServiceRuntime::json($result, ($result['success'] ?? false) ? 200 : 422);
    }
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
