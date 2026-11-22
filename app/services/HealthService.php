<?php

class HealthService
{
    private const RESOURCES = [
        'treatments' => [
            'fields' => ['animal_id', 'user_id', 'treatment_date', 'diagnosis', 'procedure_desc', 'medication', 'dosage', 'notes', 'next_control_date'],
            'required' => ['animal_id', 'treatment_date', 'diagnosis', 'procedure_desc'],
            'searchable' => ['diagnosis', 'procedure_desc', 'medication'],
            'date' => 'treatment_date',
        ],
        'vaccines' => [
            'fields' => ['animal_id', 'user_id', 'vaccine_name', 'application_date', 'next_due_date', 'batch', 'notes'],
            'required' => ['animal_id', 'vaccine_name', 'application_date'],
            'searchable' => ['vaccine_name', 'batch', 'notes'],
            'date' => 'application_date',
        ],
        'observations' => [
            'fields' => ['animal_id', 'user_id', 'observation_date', 'description'],
            'required' => ['animal_id', 'observation_date', 'description'],
            'searchable' => ['description'],
            'date' => 'observation_date',
        ],
    ];

    public static function isResource(string $resource): bool
    {
        return isset(self::RESOURCES[$resource]);
    }

    public function list(string $resource, string $search = ''): array
    {
        $config = self::RESOURCES[$resource];
        $sql = "SELECT r.*, animals.name AS animal_name, users.name AS user_name
                FROM {$resource} r
                LEFT JOIN animals ON animals.id = r.animal_id
                LEFT JOIN users ON users.id = r.user_id";
        $params = [];

        if ($search !== '') {
            $conditions = [];
            foreach ($config['searchable'] as $index => $column) {
                $conditions[] = "r.{$column} LIKE :term{$index}";
                $params["term{$index}"] = '%' . $search . '%';
            }
            $sql .= ' WHERE ' . implode(' OR ', $conditions);
        }

        $stmt = Database::pdo()->prepare($sql . " ORDER BY r.{$config['date']} DESC, r.id DESC");
        $stmt->execute($params);

        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    public function find(string $resource, int $id): array
    {
        $stmt = Database::pdo()->prepare("SELECT * FROM {$resource} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row
            ? ['success' => true, 'data' => $row]
            : ['success' => false, 'message' => 'Registro no encontrado.'];
    }

    public function create(string $resource, array $input): array
    {
        $data = $this->normalize($resource, $input);
        if ($errors = Validator::required($data, self::RESOURCES[$resource]['required'])) {
            return ['success' => false, 'message' => 'Revisa los campos obligatorios.', 'errors' => $errors];
        }

        $fields = array_keys($data);
        $stmt = Database::pdo()->prepare(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $resource,
            implode(', ', $fields),
            implode(', ', array_map(fn ($field) => ':' . $field, $fields))
        ));
        $stmt->execute($data);

        return ['success' => true, 'data' => ['id' => (int) Database::pdo()->lastInsertId()]];
    }

    public function update(string $resource, int $id, array $input): array
    {
        $data = $this->normalize($resource, $input);
        if ($errors = Validator::required($data, self::RESOURCES[$resource]['required'])) {
            return ['success' => false, 'message' => 'Revisa los campos obligatorios.', 'errors' => $errors];
        }

        $set = implode(', ', array_map(fn ($field) => "{$field} = :{$field}", array_keys($data)));
        Database::pdo()->prepare("UPDATE {$resource} SET {$set} WHERE id = :id")->execute($data + ['id' => $id]);

        return ['success' => true, 'data' => ['id' => $id]];
    }

    public function delete(string $resource, int $id): array
    {
        Database::pdo()->prepare("DELETE FROM {$resource} WHERE id = :id")->execute(['id' => $id]);
        return ['success' => true];
    }

    public function history(int $animalId, string $from = '', string $to = '', string $type = ''): array
    {
        if ($animalId <= 0) {
            return ['success' => false, 'message' => 'Selecciona un animal.'];
        }

        $parts = [
            'treatment' => "SELECT 'treatment' AS type, t.id, t.treatment_date AS event_date, t.diagnosis AS title,
                                   CONCAT_WS(' · ', t.procedure_desc, NULLIF(t.medication, ''), NULLIF(t.dosage, '')) AS detail,
                                   t.notes, t.next_control_date AS next_date, u.name AS user_name
                            FROM treatments t LEFT JOIN users u ON u.id = t.user_id WHERE t.animal_id = :animal_t",
            'vaccine' => "SELECT 'vaccine' AS type, v.id, v.application_date AS event_date, v.vaccine_name AS title,
                                 CONCAT('Lote: ', COALESCE(NULLIF(v.batch, ''), '-')) AS detail,
                                 v.notes, v.next_due_date AS next_date, u.name AS user_name
                          FROM vaccines v LEFT JOIN users u ON u.id = v.user_id WHERE v.animal_id = :animal_v",
            'observation' => "SELECT 'observation' AS type, o.id, o.observation_date AS event_date, 'Observación' AS title,
                                     o.description AS detail, NULL AS notes, NULL AS next_date, u.name AS user_name
                              FROM observations o LEFT JOIN users u ON u.id = o.user_id WHERE o.animal_id = :animal_o",
        ];

        $selected = isset($parts[$type]) ? [$type => $parts[$type]] : $parts;
        $params = [];
        foreach (array_keys($selected) as $key) {
            $params['animal_' . $key[0]] = $animalId;
        }

        $sql = 'SELECT * FROM (' . implode(' UNION ALL ', $selected) . ') history WHERE 1 = 1';
        if ($this->isDate($from)) {
            $sql .= ' AND event_date >= :from_date';
            $params['from_date'] = $from;
        }
        if ($this->isDate($to)) {
            $sql .= ' AND event_date <= :to_date';
            $params['to_date'] = $to;
        }

        $stmt = Database::pdo()->prepare($sql . ' ORDER BY event_date DESC, id DESC');
        $stmt->execute($params);

        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    public function upcoming(int $days): array
    {
        $stmt = Database::pdo()->prepare("
            SELECT 'vaccine_due' AS type, v.id, v.animal_id, a.name AS animal_name, v.vaccine_name AS title, v.next_due_date AS due_date
            FROM vaccines v JOIN animals a ON a.id = v.animal_id
            WHERE v.next_due_date IS NOT NULL AND v.next_due_date <= DATE_ADD(CURDATE(), INTERVAL :days_v DAY)
            UNION ALL
            SELECT 'treatment_control', t.id, t.animal_id, a.name, t.diagnosis, t.next_control_date
            FROM treatments t JOIN animals a ON a.id = t.animal_id
            WHERE t.next_control_date IS NOT NULL AND t.next_control_date <= DATE_ADD(CURDATE(), INTERVAL :days_t DAY)
            ORDER BY due_date ASC
        ");
        $stmt->execute(['days_v' => $days, 'days_t' => $days]);

        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    private function normalize(string $resource, array $input): array
    {
        $data = [];
        foreach (self::RESOURCES[$resource]['fields'] as $field) {
            $value = trim((string) ($input[$field] ?? ''));
            $data[$field] = $value === '' && !in_array($field, self::RESOURCES[$resource]['required'], true) ? null : $value;
        }

        return $data;
    }

    private function isDate(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }
}
