<?php

class TreatmentController extends CrudController
{
    protected string $modelClass = Treatment::class;
    protected string $viewFolder = 'treatments';
    protected string $title = 'Tratamientos';
    protected array $fillable = ['animal_id', 'user_id', 'treatment_date', 'diagnosis', 'procedure_desc', 'medication', 'dosage', 'notes', 'next_control_date'];
    protected array $searchable = ['diagnosis', 'procedure_desc', 'medication'];
    protected array $labels = [
        'animal_id' => 'Animal',
        'user_id' => 'Responsable',
        'treatment_date' => 'Fecha',
        'diagnosis' => 'Diagnóstico',
        'procedure_desc' => 'Procedimiento',
        'medication' => 'Medicamento',
        'dosage' => 'Dosis',
        'notes' => 'Notas',
        'next_control_date' => 'Próximo control',
    ];
    protected array $fieldTypes = [
        'animal_id' => 'select',
        'user_id' => 'select',
        'treatment_date' => 'date',
        'procedure_desc' => 'textarea',
        'medication' => 'textarea',
        'notes' => 'textarea',
        'next_control_date' => 'date',
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
