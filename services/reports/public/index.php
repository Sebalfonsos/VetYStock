<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new ReportService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'reports', 'status' => 'ok']);
}

$claims = ServiceRuntime::requireClaims();

if ($method === 'POST' && $route === 'audit') {
    ServiceRuntime::json($service->recordAudit(ServiceRuntime::body()), 201);
}

// Los reportes y la bitácora completos son solo para administradores.
if (!in_array($claims['role'] ?? '', ['Administrador', 'system'], true)) {
    ServiceRuntime::json(['success' => false, 'message' => 'Solo los administradores pueden ver reportes.'], 403);
}

if ($method === 'GET' && $route === 'audit') {
    ServiceRuntime::json($service->audit($_GET));
}

[$resource, $type] = array_pad(explode('/', $route, 2), 2, '');
if ($method === 'GET' && $resource === 'reports') {
    ServiceRuntime::json($service->report((string) $type, $_GET, (string) ServiceRuntime::bearerToken()));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
