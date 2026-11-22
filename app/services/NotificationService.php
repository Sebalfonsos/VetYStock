<?php

class NotificationService
{
    public function ensureSchema(): void
    {
        Database::pdo()->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(40) NOT NULL,
                source_id INT NOT NULL,
                due_date DATE NULL,
                title VARCHAR(190) NOT NULL,
                message VARCHAR(500) NOT NULL,
                link VARCHAR(190) NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                read_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY uq_notification_source (type, source_id, due_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function scan(): array
    {
        $this->ensureSchema();
        $client = new MicroserviceClient();
        $token = ServiceRuntime::systemToken('notifications');
        $days = max(1, (int) (getenv('NOTIFICATIONS_DAYS') ?: 7));
        $candidates = [];
        $errors = [];

        $upcoming = $client->get('health', 'upcoming', $token, ['days' => $days]);
        if ($upcoming['success'] ?? false) {
            foreach ($upcoming['data'] as $event) {
                $candidates[] = $this->fromHealthEvent($event);
            }
        } else {
            $errors[] = 'health: ' . ($upcoming['message'] ?? 'sin respuesta');
        }

        $alerts = $client->get('inventory', 'alerts', $token);
        if ($alerts['success'] ?? false) {
            foreach ($alerts['data'] as $alert) {
                $candidates[] = [
                    'type' => 'low_stock',
                    'source_id' => (int) $alert['id'],
                    'due_date' => substr((string) $alert['created_at'], 0, 10),
                    'title' => 'Stock bajo: ' . ($alert['product_name'] ?? 'producto'),
                    'message' => (string) $alert['message'],
                    'link' => 'inventory/index',
                ];
            }
        } else {
            $errors[] = 'inventory: ' . ($alerts['message'] ?? 'sin respuesta');
        }

        $stmt = Database::pdo()->prepare('
            INSERT IGNORE INTO notifications (type, source_id, due_date, title, message, link)
            VALUES (:type, :source_id, :due_date, :title, :message, :link)
        ');
        $created = 0;
        foreach ($candidates as $candidate) {
            $stmt->execute($candidate);
            $created += $stmt->rowCount();
        }

        return [
            'success' => true,
            'data' => ['checked' => count($candidates), 'created' => $created, 'errors' => $errors],
        ];
    }

    public function list(bool $onlyUnread): array
    {
        $this->ensureSchema();
        $sql = 'SELECT * FROM notifications' . ($onlyUnread ? ' WHERE is_read = 0' : '') . ' ORDER BY is_read ASC, due_date ASC, id DESC LIMIT 200';

        return [
            'success' => true,
            'data' => Database::pdo()->query($sql)->fetchAll(),
            'unread' => $this->unreadCount(),
        ];
    }

    public function unreadCount(): int
    {
        $this->ensureSchema();
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn();
    }

    public function markRead(?int $id): array
    {
        $this->ensureSchema();
        $sql = 'UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0';
        $params = [];
        if ($id !== null) {
            $sql .= ' AND id = :id';
            $params['id'] = $id;
        }
        Database::pdo()->prepare($sql)->execute($params);

        return ['success' => true, 'unread' => $this->unreadCount()];
    }

    private function fromHealthEvent(array $event): array
    {
        $due = (string) $event['due_date'];
        $overdue = $due < date('Y-m-d');
        $when = $overdue ? 'venció el ' . $due : 'para el ' . $due;

        return $event['type'] === 'vaccine_due'
            ? [
                'type' => 'vaccine_due',
                'source_id' => (int) $event['id'],
                'due_date' => $due,
                'title' => 'Vacuna pendiente: ' . $event['animal_name'],
                'message' => sprintf('Próxima dosis de %s %s.', $event['title'], $when),
                'link' => 'medical-history/index&animal_id=' . (int) $event['animal_id'],
            ]
            : [
                'type' => 'treatment_control',
                'source_id' => (int) $event['id'],
                'due_date' => $due,
                'title' => 'Control pendiente: ' . $event['animal_name'],
                'message' => sprintf('Control del tratamiento "%s" %s.', $event['title'], $when),
                'link' => 'medical-history/index&animal_id=' . (int) $event['animal_id'],
            ];
    }
}
