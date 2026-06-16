<?php

class AnimalService
{
    public function animals(): array
    {
        return [
            'success' => true,
            'data' => Database::pdo()->query('
                SELECT animals.*, owners.full_name AS owner_name, species.name AS species_name
                FROM animals
                LEFT JOIN owners ON owners.id = animals.owner_id
                LEFT JOIN species ON species.id = animals.species_id
                ORDER BY animals.id DESC
            ')->fetchAll(),
        ];
    }

    public function owners(): array
    {
        return ['success' => true, 'data' => (new Owner())->all('full_name ASC')];
    }

    public function species(): array
    {
        return ['success' => true, 'data' => (new Species())->all('name ASC')];
    }

    public function createAnimal(array $input): array
    {
        $data = $this->normalizeAnimal($input);
        $errors = Validator::required($data, ['owner_id', 'species_id', 'identification_code', 'name', 'sex', 'birth_date', 'status']);
        if ($errors) {
            return $this->failed($errors);
        }

        $id = (new Animal())->create($data);
        Logger::audit('create', 'animals', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function updateAnimal(int $id, array $input): array
    {
        $data = $this->normalizeAnimal($input);
        $errors = Validator::required($data, ['owner_id', 'species_id', 'identification_code', 'name', 'sex', 'birth_date', 'status']);
        if ($errors) {
            return $this->failed($errors);
        }

        (new Animal())->update($id, $data);
        Logger::audit('update', 'animals', $id, $data);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function deleteAnimal(int $id): array
    {
        (new Animal())->delete($id);
        Logger::audit('delete', 'animals', $id);
        return ['success' => true];
    }

    public function createOwner(array $input): array
    {
        $data = $this->normalizeOwner($input);
        $errors = Validator::required($data, ['full_name']);
        if ($errors) {
            return $this->failed($errors);
        }

        $id = (new Owner())->create($data);
        Logger::audit('create', 'owners', $id, $data);
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function updateOwner(int $id, array $input): array
    {
        $data = $this->normalizeOwner($input);
        $errors = Validator::required($data, ['full_name']);
        if ($errors) {
            return $this->failed($errors);
        }

        (new Owner())->update($id, $data);
        Logger::audit('update', 'owners', $id, $data);
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function deleteOwner(int $id): array
    {
        (new Owner())->delete($id);
        Logger::audit('delete', 'owners', $id);
        return ['success' => true];
    }

    public function createSpecies(array $input): array
    {
        $data = $this->normalizeSpecies($input);
        $errors = Validator::required($data, ['name']);
        if ($errors) {
            return $this->failed($errors);
        }

        $id = (new Species())->create($data);
        Logger::audit('create', 'species', $id, $data);
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function updateSpecies(int $id, array $input): array
    {
        $data = $this->normalizeSpecies($input);
        $errors = Validator::required($data, ['name']);
        if ($errors) {
            return $this->failed($errors);
        }

        (new Species())->update($id, $data);
        Logger::audit('update', 'species', $id, $data);
        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function deleteSpecies(int $id): array
    {
        (new Species())->delete($id);
        Logger::audit('delete', 'species', $id);
        return ['success' => true];
    }

    private function normalizeAnimal(array $input): array
    {
        return [
            'owner_id' => (int) ($input['owner_id'] ?? 0),
            'species_id' => (int) ($input['species_id'] ?? 0),
            'identification_code' => trim((string) ($input['identification_code'] ?? '')),
            'name' => trim((string) ($input['name'] ?? '')),
            'breed' => trim((string) ($input['breed'] ?? '')),
            'sex' => trim((string) ($input['sex'] ?? '')),
            'birth_date' => trim((string) ($input['birth_date'] ?? '')),
            'color' => trim((string) ($input['color'] ?? '')),
            'photo_path' => trim((string) ($input['photo_path'] ?? '')),
            'status' => trim((string) ($input['status'] ?? 'activo')),
        ];
    }

    private function normalizeOwner(array $input): array
    {
        return [
            'full_name' => trim((string) ($input['full_name'] ?? '')),
        ];
    }

    private function normalizeSpecies(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
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
