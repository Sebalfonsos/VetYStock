<?php

class CategoryController extends CrudController
{
    protected string $modelClass = Category::class;
    protected string $viewFolder = 'categories';
    protected string $title = 'Categorías';
    protected array $fillable = ['name', 'description', 'status'];
    protected array $searchable = ['name', 'description'];
    protected array $labels = [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'status' => 'Estado',
    ];
    protected array $fieldTypes = [
        'description' => 'textarea',
        'status' => 'select',
    ];
    protected array $selectOptions = [
        'status' => [
            ['value' => 'active', 'label' => 'Activo'],
            ['value' => 'inactive', 'label' => 'Inactivo'],
        ],
    ];
}

