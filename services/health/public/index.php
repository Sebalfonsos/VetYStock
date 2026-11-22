<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new HealthService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'health', 'status' => 'ok']);
}

ServiceRuntime::requireClaims();

[$resource, $id] = array_pad(explode('/', $route, 2), 2, null);
$id = $id !== null && $id !== '' ? (int) $id : null;

if ($method === 'GET' && $route === 'history') {
    ServiceRuntime::json($service->history(
        (int) ($_GET['animal_id'] ?? 0),
        (string) ($_GET['from'] ?? ''),
        (string) ($_GET['to'] ?? ''),
        (string) ($_GET['type'] ?? '')
    ));
}

if ($method === 'GET' && $route === 'upcoming') {
    ServiceRuntime::json($service->upcoming(max(0, (int) ($_GET['days'] ?? 7))));
}

if (HealthService::isResource((string) $resource)) {
    if ($method === 'GET' && $id === null) {
        ServiceRuntime::json($service->list($resource, trim((string) ($_GET['q'] ?? ''))));
    }
    if ($method === 'GET') {
        ServiceRuntime::json($service->find($resource, $id));
    }
    if ($method === 'POST' && $id === null) {
        ServiceRuntime::json($service->create($resource, ServiceRuntime::body()), 201);
    }
    if (in_array($method, ['PUT', 'PATCH'], true) && $id !== null) {
        ServiceRuntime::json($service->update($resource, $id, ServiceRuntime::body()));
    }
    if ($method === 'DELETE' && $id !== null) {
        ServiceRuntime::json($service->delete($resource, $id));
    }
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
