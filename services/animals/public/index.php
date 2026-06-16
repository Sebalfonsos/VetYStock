<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new AnimalService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();
$body = ServiceRuntime::body();

[$resource, $id] = array_pad(explode('/', $route, 2), 2, null);
$id = $id !== null && $id !== '' ? (int) $id : null;

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'animals', 'status' => 'ok']);
}

if ($method === 'GET' && $route === 'animals') {
    ServiceRuntime::json($service->animals());
}

if ($method === 'POST' && $route === 'animals') {
    ServiceRuntime::json($service->createAnimal($body), 201);
}

if (in_array($method, ['PUT', 'PATCH'], true) && $resource === 'animals' && $id !== null) {
    ServiceRuntime::json($service->updateAnimal($id, $body));
}

if ($method === 'DELETE' && $resource === 'animals' && $id !== null) {
    ServiceRuntime::json($service->deleteAnimal($id));
}

if ($method === 'GET' && $route === 'owners') {
    ServiceRuntime::json($service->owners());
}

if ($method === 'POST' && $route === 'owners') {
    ServiceRuntime::json($service->createOwner($body), 201);
}

if (in_array($method, ['PUT', 'PATCH'], true) && $resource === 'owners' && $id !== null) {
    ServiceRuntime::json($service->updateOwner($id, $body));
}

if ($method === 'DELETE' && $resource === 'owners' && $id !== null) {
    ServiceRuntime::json($service->deleteOwner($id));
}

if ($method === 'GET' && $route === 'species') {
    ServiceRuntime::json($service->species());
}

if ($method === 'POST' && $route === 'species') {
    ServiceRuntime::json($service->createSpecies($body), 201);
}

if (in_array($method, ['PUT', 'PATCH'], true) && $resource === 'species' && $id !== null) {
    ServiceRuntime::json($service->updateSpecies($id, $body));
}

if ($method === 'DELETE' && $resource === 'species' && $id !== null) {
    ServiceRuntime::json($service->deleteSpecies($id));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
