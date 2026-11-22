<?php

declare(strict_types=1);

$root = dirname(__DIR__, 3);
require_once $root . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$service = new AssistantService();
$route = ServiceRuntime::route();
$method = ServiceRuntime::method();

if ($route === 'health') {
    ServiceRuntime::json([
        'success' => true,
        'service' => 'assistant',
        'status' => 'ok',
        'model' => config('app.llm.model'),
    ]);
}

if ($method === 'POST' && $route === 'chat') {
    ServiceRuntime::json($service->chat(ServiceRuntime::body()));
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
