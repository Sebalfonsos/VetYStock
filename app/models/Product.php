<?php

class Product extends BaseModel
{
    protected function table(): string
    {
        return 'products';
    }

    public function allWithCategory(): array
    {
        return $this->pdo->query('SELECT products.*, categories.name AS category_name FROM products INNER JOIN categories ON categories.id = products.category_id ORDER BY products.id DESC')->fetchAll();
    }

    public function publicCatalog(): array
    {
        return $this->pdo->query('
            SELECT products.*, categories.name AS category_name
            FROM products
            INNER JOIN categories ON categories.id = products.category_id
            WHERE products.status = "active"
            ORDER BY categories.name ASC, products.name ASC
        ')->fetchAll();
    }
}
