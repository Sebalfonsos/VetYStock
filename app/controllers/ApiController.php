<?php

class ApiController extends Controller
{
    public function products(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $items = Database::pdo()->query('
            SELECT products.*, categories.name AS category_name
            FROM products
            INNER JOIN categories ON categories.id = products.category_id
            ORDER BY categories.name ASC, products.name ASC
        ')->fetchAll();

        echo json_encode([
            'success' => true,
            'count' => count($items),
            'data' => $items,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

