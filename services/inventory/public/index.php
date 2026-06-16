<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new InventoryService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();
$body = ServiceRuntime::body();

[$resource, $id, $extra] = array_pad(explode('/', $route, 3), 3, null);
$id = $id !== null && $id !== '' ? (int) $id : null;

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'inventory', 'status' => 'ok']);
}

$token = ServiceRuntime::bearerToken();
if (!$token) {
    ServiceRuntime::json(['success' => false, 'message' => 'Token requerido.'], 401);
}

$claims = ServiceToken::validate($token);
if (!$claims) {
    ServiceRuntime::json(['success' => false, 'message' => 'Token inválido o expirado.'], 401);
}

$userId = (int) ($claims['sub'] ?? 0);

if ($method === 'GET' && $route === 'movements') {
    ServiceRuntime::json($service->movements());
}

if ($method === 'POST' && $route === 'movements') {
    ServiceRuntime::json($service->createMovement($body, $userId), 201);
}

if ($method === 'GET' && $route === 'alerts') {
    ServiceRuntime::json($service->alerts());
}

if (in_array($method, ['POST', 'PATCH'], true) && $resource === 'alerts' && $id !== null && $extra === 'resolve') {
    ServiceRuntime::json($service->resolveAlert($id));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
