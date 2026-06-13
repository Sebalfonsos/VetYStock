<?php

class VaccineController extends CrudController
{
    protected string $modelClass = Vaccine::class;
    protected string $viewFolder = 'vaccines';
    protected string $title = 'Vacunas';
    protected array $fillable = ['animal_id', 'user_id', 'vaccine_name', 'application_date', 'next_due_date', 'batch', 'notes'];
    protected array $searchable = ['vaccine_name', 'batch', 'notes'];
    protected array $labels = [
        'animal_id' => 'Animal',
        'user_id' => 'Responsable',
        'vaccine_name' => 'Vacuna',
        'application_date' => 'Aplicación',
        'next_due_date' => 'Próxima dosis',
        'batch' => 'Lote',
        'notes' => 'Notas',
    ];
    protected array $fieldTypes = [
        'animal_id' => 'select',
        'user_id' => 'select',
        'application_date' => 'date',
        'next_due_date' => 'date',
        'notes' => 'textarea',
    ];

    public function create(): void
    {
        $this->selectOptions = [
            'animal_id' => array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name'] . ' - ' . $row['identification_code']], (new Animal())->all('name ASC')),
            'user_id' => array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name']], (new User())->all('name ASC')),
        ];
        parent::create();
    }

    public function edit(): void
    {
        $this->selectOptions = [
            'animal_id' => array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name'] . ' - ' . $row['identification_code']], (new Animal())->all('name ASC')),
            'user_id' => array_map(fn ($row) => ['value' => $row['id'], 'label' => $row['name']], (new User())->all('name ASC')),
        ];
        parent::edit();
    }
}

