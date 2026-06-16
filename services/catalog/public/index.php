<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new CatalogService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();
$body = ServiceRuntime::body();

[$resource, $id] = array_pad(explode('/', $route, 2), 2, null);
$id = $id !== null && $id !== '' ? (int) $id : null;

if ($route === 'health') {
    ServiceRuntime::json(['success' => true, 'service' => 'catalog', 'status' => 'ok']);
}

if ($method === 'GET' && $resource === 'categories') {
    ServiceRuntime::json(['success' => true, 'data' => $service->categories()]);
}

if ($method === 'POST' && $resource === 'categories') {
    ServiceRuntime::json($service->createCategory($body), 201);
}

if ($method === 'PUT' && $resource === 'categories' && $id !== null) {
    ServiceRuntime::json($service->updateCategory($id, $body));
}

if ($method === 'DELETE' && $resource === 'categories' && $id !== null) {
    ServiceRuntime::json($service->deleteCategory($id));
}

if ($method === 'GET' && $resource === 'products') {
    ServiceRuntime::json($service->publicCatalog());
}

if ($method === 'POST' && $resource === 'products') {
    ServiceRuntime::json($service->createProduct($body), 201);
}

if ($method === 'PUT' && $resource === 'products' && $id !== null) {
    ServiceRuntime::json($service->updateProduct($id, $body));
}

if ($method === 'DELETE' && $resource === 'products' && $id !== null) {
    ServiceRuntime::json($service->deleteProduct($id));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
