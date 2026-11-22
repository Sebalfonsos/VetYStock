<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new NotificationService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'notifications', 'status' => 'ok']);
}

ServiceRuntime::requireClaims();

[$resource, $id, $action] = array_pad(explode('/', $route, 3), 3, null);
$id = $id !== null && ctype_digit($id) ? (int) $id : null;

if ($method === 'GET' && $route === 'notifications') {
    ServiceRuntime::json($service->list(($_GET['unread'] ?? '') === '1'));
}

if ($method === 'GET' && $route === 'notifications/count') {
    ServiceRuntime::json(['success' => true, 'unread' => $service->unreadCount()]);
}

if ($method === 'POST' && $route === 'notifications/read-all') {
    ServiceRuntime::json($service->markRead(null));
}

if ($method === 'POST' && $resource === 'notifications' && $id !== null && $action === 'read') {
    ServiceRuntime::json($service->markRead($id));
}

if ($method === 'POST' && $route === 'scan') {
    ServiceRuntime::json($service->scan());
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
