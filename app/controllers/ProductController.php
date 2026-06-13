<?php

class ProductController extends CrudController
{
    protected string $modelClass = Product::class;
    protected string $viewFolder = 'products';
    protected string $title = 'Productos';
    protected array $fillable = ['category_id', 'animal_group', 'code', 'name', 'description', 'unit_cost', 'sale_price', 'current_stock', 'stock_min', 'stock_max', 'status'];
    protected array $searchable = ['code', 'name', 'description', 'animal_group'];
    protected array $labels = [
        'category_id' => 'Categoría',
        'animal_group' => 'Grupo de animal',
        'code' => 'Código',
        'name' => 'Nombre',
        'description' => 'Descripción',
        'unit_cost' => 'Costo unitario',
        'sale_price' => 'Precio venta',
        'current_stock' => 'Stock actual',
        'stock_min' => 'Stock mínimo',
        'stock_max' => 'Stock máximo',
        'status' => 'Estado',
    ];
    protected array $fieldTypes = [
        'description' => 'textarea',
        'category_id' => 'select',
        'animal_group' => 'select',
        'status' => 'select',
        'unit_cost' => 'number',
        'sale_price' => 'number',
        'current_stock' => 'number',
        'stock_min' => 'number',
        'stock_max' => 'number',
    ];
    protected array $selectOptions = [
        'animal_group' => [
            ['value' => 'domestic', 'label' => 'Doméstico'],
            ['value' => 'wild', 'label' => 'Salvaje'],
            ['value' => 'farm', 'label' => 'Granja'],
        ],
        'status' => [
            ['value' => 'active', 'label' => 'Activo'],
            ['value' => 'inactive', 'label' => 'Inactivo'],
        ],
    ];

    public function create(): void
    {
        $this->selectOptions['category_id'] = array_map(
            fn ($row) => ['value' => $row['id'], 'label' => $row['name']],
            (new Category())->all('name ASC')
        );
        parent::create();
    }

    public function edit(): void
    {
        $this->selectOptions['category_id'] = array_map(
            fn ($row) => ['value' => $row['id'], 'label' => $row['name']],
            (new Category())->all('name ASC')
        );
        parent::edit();
    }
}

