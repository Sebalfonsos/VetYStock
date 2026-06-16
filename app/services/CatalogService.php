<?php

class CatalogService
{
    public function products(): array
    {
        return Database::pdo()->query('
            SELECT products.*, categories.name AS category_name
            FROM products
            INNER JOIN categories ON categories.id = products.category_id
            ORDER BY categories.name ASC, products.name ASC
        ')->fetchAll();
    }
}
