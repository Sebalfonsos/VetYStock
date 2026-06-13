<?php

class InventoryController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $items = Database::pdo()->query('
            SELECT inventory_movements.*, products.name AS product_name, users.name AS user_name
            FROM inventory_movements
            LEFT JOIN products ON products.id = inventory_movements.product_id
            LEFT JOIN users ON users.id = inventory_movements.user_id
            ORDER BY inventory_movements.id DESC
        ')->fetchAll();

        $products = (new Product())->all('name ASC');
        $this->render('inventory/index', [
            'title' => 'Inventario',
            'items' => $items,
            'products' => $products,
            'alerts' => Database::pdo()->query('
                SELECT stock_alerts.*, products.name AS product_name
                FROM stock_alerts
                LEFT JOIN products ON products.id = stock_alerts.product_id
                WHERE stock_alerts.is_resolved = 0
                ORDER BY stock_alerts.id DESC
            ')->fetchAll(),
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::handle();

        $productId = (int) ($_POST['product_id'] ?? 0);
        $movementType = trim((string) ($_POST['movement_type'] ?? ''));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $reference = trim((string) ($_POST['reference'] ?? ''));

        if ($productId <= 0 || !in_array($movementType, ['input', 'output'], true)) {
            Session::setFlash('error', 'Completa el formulario de movimiento.');
            redirect('inventory/index');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $product = (new Product())->find($productId);
        if (!$product) {
            $pdo->rollBack();
            Session::setFlash('error', 'Producto inválido.');
            redirect('inventory/index');
        }

        $before = (int) $product['current_stock'];
        $after = $movementType === 'input' ? $before + $quantity : $before - $quantity;
        if ($after < 0) {
            $pdo->rollBack();
            Session::setFlash('error', 'No hay stock suficiente.');
            redirect('inventory/index');
        }

        $stmt = $pdo->prepare('INSERT INTO inventory_movements (product_id, user_id, movement_type, quantity, before_stock, after_stock, reason, reference, created_at) VALUES (:product_id, :user_id, :movement_type, :quantity, :before_stock, :after_stock, :reason, :reference, NOW())');
        $stmt->execute([
            'product_id' => $productId,
            'user_id' => Auth::id(),
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
            $pdo->prepare('UPDATE stock_alerts SET is_resolved = 1, resolved_at = NOW() WHERE id = :id')->execute(['id' => $existingAlert['id']]);
        }

        $pdo->commit();
        Logger::audit('movement', 'inventory', $productId, compact('movementType', 'quantity', 'reason', 'reference'));
        Session::setFlash('success', 'Movimiento registrado correctamente.');
        redirect('inventory/index');
    }
}
