<?php

class Animal extends BaseModel
{
    protected function table(): string
    {
        return 'animals';
    }

    public function allWithRelations(): array
    {
        return $this->pdo->query('SELECT animals.*, owners.full_name AS owner_name, species.name AS species_name FROM animals LEFT JOIN owners ON owners.id = animals.owner_id LEFT JOIN species ON species.id = animals.species_id ORDER BY animals.id DESC')->fetchAll();
    }
}

