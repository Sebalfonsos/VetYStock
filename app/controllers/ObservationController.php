<?php

class ObservationController extends CrudController
{
    protected string $modelClass = Observation::class;
    protected string $viewFolder = 'observations';
    protected string $title = 'Observaciones';
    protected array $fillable = ['animal_id', 'user_id', 'observation_date', 'description'];
    protected array $searchable = ['description'];
    protected array $labels = [
        'animal_id' => 'Animal',
        'user_id' => 'Responsable',
        'observation_date' => 'Fecha',
        'description' => 'Observación',
    ];
    protected array $fieldTypes = [
        'animal_id' => 'select',
        'user_id' => 'select',
        'observation_date' => 'date',
        'description' => 'textarea',
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

