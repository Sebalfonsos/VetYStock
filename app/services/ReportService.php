<?php

class ReportService
{
    private MicroserviceClient $client;

    public function __construct()
    {
        $this->client = new MicroserviceClient();
    }

    public function recordAudit(array $input): array
    {
        $action = trim((string) ($input['action'] ?? ''));
        $entity = trim((string) ($input['entity'] ?? ''));
        if ($action === '' || $entity === '') {
            return ['success' => false, 'message' => 'Faltan la acción o la entidad.'];
        }

        Database::pdo()->prepare('
            INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, :entity, :entity_id, :details, :ip_address, :user_agent, NOW())
        ')->execute([
            'user_id' => ($input['user_id'] ?? null) ? (int) $input['user_id'] : null,
            'action' => mb_substr($action, 0, 80),
            'entity' => mb_substr($entity, 0, 80),
            'entity_id' => isset($input['entity_id']) ? (int) $input['entity_id'] : null,
            'details' => json_encode($input['details'] ?? [], JSON_UNESCAPED_UNICODE),
            'ip_address' => mb_substr((string) ($input['ip_address'] ?? ''), 0, 45),
            'user_agent' => mb_substr((string) ($input['user_agent'] ?? ''), 0, 255),
        ]);

        return ['success' => true, 'data' => ['id' => (int) Database::pdo()->lastInsertId()]];
    }

    public function audit(array $filters): array
    {
        $sql = 'SELECT audit_logs.*, users.name AS user_name FROM audit_logs LEFT JOIN users ON users.id = audit_logs.user_id WHERE 1 = 1';
        $params = [];

        foreach (['user_id' => 'audit_logs.user_id', 'action' => 'audit_logs.action', 'entity' => 'audit_logs.entity'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $sql .= " AND {$column} = :{$key}";
                $params[$key] = $filters[$key];
            }
        }
        [$sql, $params] = $this->dateRange($sql, $params, 'DATE(audit_logs.created_at)', $filters);

        $stmt = Database::pdo()->prepare($sql . ' ORDER BY audit_logs.id DESC LIMIT 500');
        $stmt->execute($params);

        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    public function report(string $type, array $filters, string $token): array
    {
        return match ($type) {
            'inventory' => $this->inventory($filters),
            'movements' => $this->movements($filters, $token),
            'animal-history' => $this->animalHistory($filters, $token),
            'consultations' => $this->consultations($filters),
            'audit' => $this->auditReport($filters),
            default => ['success' => false, 'message' => 'Reporte desconocido.'],
        };
    }

    private function inventory(array $filters): array
    {
        $products = $this->fetch('catalog', 'products');
        if (isset($products['message'])) {
            return $products;
        }

        $rows = [];
        foreach ($products as $product) {
            if (($filters['category'] ?? '') !== '' && (string) $product['category_id'] !== (string) $filters['category']) {
                continue;
            }
            $stock = (int) $product['current_stock'];
            $rows[] = [
                'code' => $product['code'],
                'name' => $product['name'],
                'category' => $product['category_name'],
                'stock' => $stock,
                'stock_min' => (int) $product['stock_min'],
                'status' => $stock <= (int) $product['stock_min'] ? 'Reponer' : 'OK',
                'value' => $stock * (float) $product['unit_cost'],
            ];
        }

        return $this->table([
            'code' => 'Código', 'name' => 'Producto', 'category' => 'Categoría', 'stock' => 'Existencias',
            'stock_min' => 'Mínimo', 'status' => 'Estado', 'value' => 'Valor en bodega',
        ], $rows);
    }

    private function movements(array $filters, string $token): array
    {
        $movements = $this->fetch('inventory', 'movements', $token);
        if (isset($movements['message'])) {
            return $movements;
        }

        $from = $filters['from'] ?? '';
        $to = $filters['to'] ?? '';
        $rows = [];
        foreach ($movements as $movement) {
            $date = substr((string) $movement['created_at'], 0, 10);
            if (($from !== '' && $date < $from) || ($to !== '' && $date > $to)) {
                continue;
            }
            if (($filters['movement_type'] ?? '') !== '' && $movement['movement_type'] !== $filters['movement_type']) {
                continue;
            }
            $rows[] = [
                'date' => $movement['created_at'],
                'product' => $movement['product_name'],
                'type' => $movement['movement_type'] === 'input' ? 'Entrada' : 'Salida',
                'quantity' => (int) $movement['quantity'],
                'before' => (int) $movement['before_stock'],
                'after' => (int) $movement['after_stock'],
                'reason' => $movement['reason'],
                'user' => $movement['user_name'],
            ];
        }

        return $this->table([
            'date' => 'Fecha', 'product' => 'Producto', 'type' => 'Tipo', 'quantity' => 'Cantidad',
            'before' => 'Stock antes', 'after' => 'Stock después', 'reason' => 'Motivo', 'user' => 'Responsable',
        ], $rows);
    }

    private function animalHistory(array $filters, string $token): array
    {
        if ((int) ($filters['animal_id'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Selecciona un animal.'];
        }

        $events = $this->fetch('health', 'history', $token, array_intersect_key($filters, array_flip(['animal_id', 'from', 'to', 'type'])));
        if (isset($events['message'])) {
            return $events;
        }

        $types = ['treatment' => 'Tratamiento', 'vaccine' => 'Vacuna', 'observation' => 'Observación'];
        $rows = array_map(fn (array $event) => [
            'date' => $event['event_date'],
            'type' => $types[$event['type']] ?? $event['type'],
            'title' => $event['title'],
            'detail' => trim($event['detail'] . ' ' . ($event['notes'] ?? '')),
            'next' => $event['next_date'],
            'user' => $event['user_name'],
        ], $events);

        return $this->table([
            'date' => 'Fecha', 'type' => 'Tipo', 'title' => 'Registro', 'detail' => 'Detalle',
            'next' => 'Próxima fecha', 'user' => 'Responsable',
        ], $rows);
    }

    private function consultations(array $filters): array
    {
        $logs = $this->audit(['entity' => 'assistant', 'action' => 'consultation'] + $filters)['data'];
        $rows = array_map(function (array $log) {
            $details = json_decode((string) $log['details'], true) ?: [];
            return [
                'date' => $log['created_at'],
                'user' => $log['user_name'] ?? 'Visitante',
                'question' => $details['question'] ?? '',
                'products' => implode(', ', $details['products'] ?? []),
            ];
        }, $logs);

        return $this->table([
            'date' => 'Fecha', 'user' => 'Usuario', 'question' => 'Consulta', 'products' => 'Productos sugeridos',
        ], $rows);
    }

    private function auditReport(array $filters): array
    {
        $rows = array_map(fn (array $log) => [
            'date' => $log['created_at'],
            'user' => $log['user_name'] ?? 'Sin sesión',
            'action' => $log['action'],
            'entity' => $log['entity'],
            'entity_id' => $log['entity_id'],
            'ip' => $log['ip_address'],
        ], $this->audit($filters)['data']);

        return $this->table([
            'date' => 'Fecha', 'user' => 'Usuario', 'action' => 'Acción', 'entity' => 'Módulo',
            'entity_id' => 'Registro', 'ip' => 'IP',
        ], $rows);
    }

    private function fetch(string $service, string $route, ?string $token = null, array $query = []): array
    {
        $response = $this->client->get($service, $route, $token, $query);
        if (!($response['success'] ?? false)) {
            return ['success' => false, 'message' => sprintf('No se pudo consultar %s: %s', $service, $response['message'] ?? 'sin respuesta')];
        }

        return $response['data'] ?? [];
    }

    private function table(array $columns, array $rows): array
    {
        return ['success' => true, 'data' => ['columns' => $columns, 'rows' => array_values($rows)]];
    }

    private function dateRange(string $sql, array $params, string $column, array $filters): array
    {
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($filters[$key] ?? ''))) {
                $sql .= " AND {$column} {$operator} :{$key}";
                $params[$key] = $filters[$key];
            }
        }

        return [$sql, $params];
    }
}
