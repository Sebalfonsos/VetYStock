<?php

class ApiController extends Controller
{
    public function products(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $items = (new CatalogService())->products();

        echo json_encode([
            'success' => true,
            'count' => count($items),
            'data' => $items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

