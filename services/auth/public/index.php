<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new AuthService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();
$body = ServiceRuntime::body();

[$resource, $id] = array_pad(explode('/', $route, 2), 2, null);
$id = $id !== null && $id !== '' ? (int) $id : null;

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'auth', 'status' => 'ok']);
}

if ($method === 'POST' && $route === 'login') {
    ServiceRuntime::json($service->login((string) ($body['email'] ?? ''), (string) ($body['password'] ?? '')));
}

if ($method === 'GET' && $route === 'me') {
    $token = ServiceRuntime::bearerToken();
    if (!$token) {
        ServiceRuntime::json(['success' => false, 'message' => 'Token requerido.'], 401);
    }
    ServiceRuntime::json($service->me($token));
}

if ($method === 'GET' && $route === 'roles') {
    ServiceRuntime::json($service->roles());
}

if ($method === 'GET' && $route === 'users') {
    ServiceRuntime::json($service->users());
}

if ($method === 'POST' && $route === 'users') {
    ServiceRuntime::json($service->createUser($body), 201);
}

if (in_array($method, ['PUT', 'PATCH'], true) && $resource === 'users' && $id !== null) {
    ServiceRuntime::json($service->updateUser($id, $body));
}

if ($method === 'DELETE' && $resource === 'users' && $id !== null) {
    ServiceRuntime::json($service->deleteUser($id));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
