<?php

class AnimalController extends CrudController
{
    protected string $modelClass = Animal::class;
    protected string $viewFolder = 'animals';
    protected string $title = 'Animales';
    protected array $fillable = ['owner_id', 'species_id', 'identification_code', 'name', 'breed', 'sex', 'birth_date', 'color', 'photo_path', 'status'];
    protected array $searchable = ['identification_code', 'name', 'breed'];
    protected array $labels = [
        'owner_id' => 'Propietario',
        'species_id' => 'Especie',
        'identification_code' => 'Código',
        'name' => 'Nombre',
        'breed' => 'Raza',
        'sex' => 'Sexo',
        'birth_date' => 'Fecha de nacimiento',
        'color' => 'Color',
        'photo_path' => 'Foto',
        'status' => 'Estado',
    ];
    protected array $fieldTypes = [
        'owner_id' => 'select',
        'species_id' => 'select',
        'sex' => 'select',
        'birth_date' => 'date',
        'photo_path' => 'text',
        'status' => 'select',
    ];
    protected array $selectOptions = [
        'sex' => [
            ['value' => 'Macho', 'label' => 'Macho'],
            ['value' => 'Hembra', 'label' => 'Hembra'],
        ],
        'status' => [
            ['value' => 'activo', 'label' => 'Activo'],
            ['value' => 'inactivo', 'label' => 'Inactivo'],
        ],
    ];

    public function index(): void
    {
        AuthMiddleware::handle();
        $items = (new Animal())->allWithRelations();
        $this->render('animals/index', [
            'title' => $this->title,
            'items' => $items,
            'query' => trim((string) ($_GET['q'] ?? '')),
            'labels' => $this->labels,
            'extraColumns' => [],
            'fieldTypes' => $this->fieldTypes,
            'routeBase' => $this->viewFolder,
        ]);
    }

    public function create(): void
    {
        $this->selectOptions['owner_id'] = array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['full_name']], (new Owner())->all('full_name ASC'));
        $this->selectOptions['species_id'] = array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name']], (new Species())->all('name ASC'));
        parent::create();
    }

    public function edit(): void
    {
        $this->selectOptions['owner_id'] = array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['full_name']], (new Owner())->all('full_name ASC'));
        $this->selectOptions['species_id'] = array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name']], (new Species())->all('name ASC'));
        parent::edit();
    }
}
