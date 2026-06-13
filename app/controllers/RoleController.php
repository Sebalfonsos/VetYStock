<?php

class RoleController extends CrudController
{
    protected string $modelClass = Role::class;
    protected string $viewFolder = 'roles';
    protected string $title = 'Roles';
    protected array $fillable = ['name', 'description'];
    protected array $searchable = ['name', 'description'];
    protected array $labels = [
        'name' => 'Nombre',
        'description' => 'Descripción',
    ];
    protected array $fieldTypes = [
        'description' => 'textarea',
    ];
}

