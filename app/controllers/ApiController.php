<?php

class ApiController extends Controller
{
    public function products(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $response = (new MicroserviceClient())->get('catalog', 'products');
        $items = $response['data'] ?? [];

        echo json_encode([
            'success' => (bool) ($response['success'] ?? false),
            'count' => count($items),
            'data' => $items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

