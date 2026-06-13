<?php

abstract class BaseModel
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::pdo();
    }

    abstract protected function table(): string;

    public function all(string $orderBy = 'id DESC'): array
    {
        return $this->pdo->query("SELECT * FROM {$this->table()} ORDER BY {$orderBy}")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table()} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function where(string $column, mixed $value, string $orderBy = 'id DESC'): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table()} WHERE {$column} = :value ORDER BY {$orderBy}");
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    public function search(array $columns, string $term): array
    {
        $conditions = [];
        $params = [];
        foreach ($columns as $index => $column) {
            $conditions[] = "{$column} LIKE :term{$index}";
            $params["term{$index}"] = '%' . $term . '%';
        }

        $sql = "SELECT * FROM {$this->table()} WHERE " . implode(' OR ', $conditions) . " ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->table()}")->fetchColumn();
    }

    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn ($field) => ':' . $field, $fields);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table(),
            implode(', ', $fields),
            implode(', ', $placeholders)
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach (array_keys($data) as $field) {
            $set[] = "{$field} = :{$field}";
        }
        $data['id'] = $id;
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table(), implode(', ', $set));
        return $this->pdo->prepare($sql)->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table()} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function hasColumn(string $column): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :table_name
              AND COLUMN_NAME = :column_name
        ");
        $stmt->execute([
            'table_name' => $this->table(),
            'column_name' => $column,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
