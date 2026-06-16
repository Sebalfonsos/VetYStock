<?php

class InventoryService
{
    public function movements(): array
    {
        return [
            'success' => true,
            'data' => Database::pdo()->query('
                SELECT inventory_movements.*, products.name AS product_name, users.name AS user_name
                FROM inventory_movements
                LEFT JOIN products ON products.id = inventory_movements.product_id
                LEFT JOIN users ON users.id = inventory_movements.user_id
                ORDER BY inventory_movements.id DESC
            ')->fetchAll(),
        ];
    }

    public function alerts(): array
    {
        return [
            'success' => true,
            'data' => Database::pdo()->query('
                SELECT stock_alerts.*, products.name AS product_name
                FROM stock_alerts
                LEFT JOIN products ON products.id = stock_alerts.product_id
                WHERE stock_alerts.is_resolved = 0
                ORDER BY stock_alerts.id DESC
            ')->fetchAll(),
        ];
    }

    public function createMovement(array $input, int $userId): array
    {
        $productId = (int) ($input['product_id'] ?? 0);
        $movementType = trim((string) ($input['movement_type'] ?? ''));
        $quantity = max(1, (int) ($input['quantity'] ?? 1));
        $reason = trim((string) ($input['reason'] ?? ''));
        $reference = trim((string) ($input['reference'] ?? ''));

        if ($productId <= 0 || !in_array($movementType, ['input', 'output'], true)) {
            return $this->failed('Completa el formulario de movimiento.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $product = (new Product())->find($productId);
        if (!$product) {
            $pdo->rollBack();
            return $this->failed('Producto inválido.');
        }

        $before = (int) $product['current_stock'];
        $after = $movementType === 'input' ? $before + $quantity : $before - $quantity;
        if ($after < 0) {
            $pdo->rollBack();
            return $this->failed('No hay stock suficiente.');
        }

        $pdo->prepare('INSERT INTO inventory_movements (product_id, user_id, movement_type, quantity, before_stock, after_stock, reason, reference, created_at) VALUES (:product_id, :user_id, :movement_type, :quantity, :before_stock, :after_stock, :reason, :reference, NOW())')->execute([
            'product_id' => $productId,
            'user_id' => $userId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'before_stock' => $before,
            'after_stock' => $after,
            'reason' => $reason,
            'reference' => $reference,
        ]);

        $pdo->prepare('UPDATE products SET current_stock = :current_stock WHERE id = :id')->execute([
            'current_stock' => $after,
            'id' => $productId,
        ]);

        $alertStmt = $pdo->prepare('SELECT id FROM stock_alerts WHERE product_id = :product_id AND is_resolved = 0 ORDER BY id DESC LIMIT 1');
        $alertStmt->execute(['product_id' => $productId]);
        $existingAlert = $alertStmt->fetch();

        if ($after <= (int) $product['stock_min']) {
            if (!$existingAlert) {
                $pdo->prepare('INSERT INTO stock_alerts (product_id, alert_type, message, is_resolved, created_at) VALUES (:product_id, :alert_type, :message, 0, NOW())')->execute([
                    'product_id' => $productId,
                    'alert_type' => 'low_stock',
                    'message' => 'Stock mínimo alcanzado para ' . $product['name'],
                ]);
            }
        } elseif ($existingAlert) {
            $pdo->prepare('UPDATE stock_alerts SET is_resolved = 1, resolved_at = NOW() WHERE id = :id')->execute([
                'id' => $existingAlert['id'],
            ]);
        }

        $pdo->commit();
        Logger::audit('movement', 'inventory', $productId, compact('movementType', 'quantity', 'reason', 'reference'));

        return [
            'success' => true,
            'data' => [
                'product_id' => $productId,
                'before_stock' => $before,
                'after_stock' => $after,
            ],
        ];
    }

    public function resolveAlert(int $id): array
    {
        Database::pdo()->prepare('UPDATE stock_alerts SET is_resolved = 1, resolved_at = NOW() WHERE id = :id')->execute([
            'id' => $id,
        ]);

        Logger::audit('resolve', 'stock_alerts', $id);

        return ['success' => true];
    }

    private function failed(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
