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

    public function categories(): array
    {
        return (new Category())->all('name ASC');
    }

    public function createCategory(array $input): array
    {
        $data = $this->normalizeCategory($input);
        $errors = Validator::required($data, ['name', 'description', 'status']);
        if ($errors) {
            return $this->failed($errors);
        }

        $id = (new Category())->create($data);
        Logger::audit('create', 'categories', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function updateCategory(int $id, array $input): array
    {
        $data = $this->normalizeCategory($input);
        $errors = Validator::required($data, ['name', 'description', 'status']);
        if ($errors) {
            return $this->failed($errors);
        }

        (new Category())->update($id, $data);
        Logger::audit('update', 'categories', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function deleteCategory(int $id): array
    {
        (new Category())->delete($id);
        Logger::audit('delete', 'categories', $id);
        return ['success' => true];
    }

    public function createProduct(array $input): array
    {
        $data = $this->normalizeProduct($input);
        $errors = Validator::required($data, [
            'category_id',
            'animal_group',
            'code',
            'name',
            'description',
            'unit_cost',
            'sale_price',
            'current_stock',
            'stock_min',
            'stock_max',
            'status',
        ]);

        if ($errors) {
            return $this->failed($errors);
        }

        $id = (new Product())->create($data);
        Logger::audit('create', 'products', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function updateProduct(int $id, array $input): array
    {
        $data = $this->normalizeProduct($input);
        $errors = Validator::required($data, [
            'category_id',
            'animal_group',
            'code',
            'name',
            'description',
            'unit_cost',
            'sale_price',
            'current_stock',
            'stock_min',
            'stock_max',
            'status',
        ]);

        if ($errors) {
            return $this->failed($errors);
        }

        (new Product())->update($id, $data);
        Logger::audit('update', 'products', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function deleteProduct(int $id): array
    {
        (new Product())->delete($id);
        Logger::audit('delete', 'products', $id);
        return ['success' => true];
    }

    public function publicCatalog(): array
    {
        return [
            'success' => true,
            'count' => count($this->products()),
            'data' => $this->products(),
        ];
    }

    private function normalizeCategory(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'status' => trim((string) ($input['status'] ?? 'active')),
        ];
    }

    private function normalizeProduct(array $input): array
    {
        return [
            'category_id' => (int) ($input['category_id'] ?? 0),
            'animal_group' => trim((string) ($input['animal_group'] ?? '')),
            'code' => trim((string) ($input['code'] ?? '')),
            'name' => trim((string) ($input['name'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'unit_cost' => (float) ($input['unit_cost'] ?? 0),
            'sale_price' => (float) ($input['sale_price'] ?? 0),
            'current_stock' => (int) ($input['current_stock'] ?? 0),
            'stock_min' => (int) ($input['stock_min'] ?? 0),
            'stock_max' => (int) ($input['stock_max'] ?? 0),
            'status' => trim((string) ($input['status'] ?? 'active')),
        ];
    }

    private function failed(array $errors): array
    {
        return [
            'success' => false,
            'message' => 'Revisa los campos obligatorios.',
            'errors' => $errors,
        ];
    }
}
