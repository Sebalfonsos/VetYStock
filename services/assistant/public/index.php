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

if ($method === 'POST' && $route === 'chat/stream') {
    $body = ServiceRuntime::body();
    ServiceRuntime::startStream();
    $result = $service->chat($body, fn (string $type, string $text) => ServiceRuntime::streamEvent(['type' => $type, 'text' => $text]));
    ServiceRuntime::streamEvent(($result['success'] ?? false)
        ? ['type' => 'done'] + $result['data']
        : ['type' => 'error', 'message' => $result['message'] ?? 'No se pudo obtener respuesta.']);
    exit;
}

ServiceRuntime::json([
    'success' => false,
    'message' => 'Ruta no encontrada',
], 404);
