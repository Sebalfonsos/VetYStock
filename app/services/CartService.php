<?php

class CartService
{
    private MicroserviceClient $client;

    public function __construct()
    {
        $this->client = new MicroserviceClient();
    }

    public function ensureSchema(): void
    {
        $pdo = Database::pdo();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS cart_items (
                cart_key CHAR(32) NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                added_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (cart_key, product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                customer_name VARCHAR(120) NOT NULL,
                customer_phone VARCHAR(40) NOT NULL,
                notes VARCHAR(500) NULL,
                total DECIMAL(12,2) NOT NULL DEFAULT 0,
                status ENUM('pending','delivered','cancelled') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                KEY idx_orders_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(150) NOT NULL,
                unit_price DECIMAL(10,2) NOT NULL,
                quantity INT NOT NULL,
                CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Devuelve el carrito con precios y existencias tomados del catálogo en este momento, nunca del navegador.
    public function cart(string $key): array
    {
        $catalog = $this->catalog();
        if (isset($catalog['message'])) {
            return $catalog;
        }

        $stmt = Database::pdo()->prepare('SELECT product_id, quantity FROM cart_items WHERE cart_key = :key ORDER BY added_at, product_id');
        $stmt->execute(['key' => $key]);

        $items = [];
        $total = 0.0;
        $count = 0;
        foreach ($stmt->fetchAll() as $row) {
            $product = $catalog[(int) $row['product_id']] ?? null;
            $quantity = (int) $row['quantity'];
            $stock = $product ? (int) $product['current_stock'] : 0;
            $available = $product && $product['status'] === 'active';
            $price = $product ? (float) $product['sale_price'] : 0.0;

            $items[] = [
                'product_id' => (int) $row['product_id'],
                'code' => $product['code'] ?? '',
                'name' => $product['name'] ?? 'Producto no disponible',
                'category' => $product['category_name'] ?? '',
                'price' => $price,
                'quantity' => $quantity,
                'stock' => $stock,
                'subtotal' => $price * $quantity,
                'problem' => !$available ? 'Ya no está disponible.' : ($quantity > $stock ? 'Solo hay ' . $stock . ' disponibles.' : null),
            ];
            $total += $price * $quantity;
            $count += $quantity;
        }

        return ['success' => true, 'data' => ['items' => $items, 'count' => $count, 'total' => $total]];
    }

    // $mode 'add' suma a lo que ya había; 'set' deja la cantidad exacta (0 lo quita).
    public function putItem(string $key, array $input, string $mode): array
    {
        $productId = (int) ($input['product_id'] ?? 0);
        $quantity = (int) ($input['quantity'] ?? 1);
        if ($productId <= 0 || $quantity < 0 || ($mode === 'add' && $quantity < 1)) {
            return ['success' => false, 'message' => 'Producto o cantidad inválidos.'];
        }

        $catalog = $this->catalog();
        if (isset($catalog['message'])) {
            return $catalog;
        }

        $product = $catalog[$productId] ?? null;
        if (!$product || $product['status'] !== 'active') {
            return ['success' => false, 'message' => 'Ese producto no está disponible.'];
        }

        $pdo = Database::pdo();
        $current = $pdo->prepare('SELECT quantity FROM cart_items WHERE cart_key = :key AND product_id = :product');
        $current->execute(['key' => $key, 'product' => $productId]);
        $newQuantity = $mode === 'add' ? (int) $current->fetchColumn() + $quantity : $quantity;

        if ($newQuantity === 0) {
            return $this->removeItem($key, $productId);
        }

        $stock = (int) $product['current_stock'];
        if ($newQuantity > $stock) {
            return ['success' => false, 'message' => $stock > 0
                ? 'Solo hay ' . $stock . ' unidades de ' . $product['name'] . '.'
                : $product['name'] . ' está agotado.'];
        }

        $pdo->prepare('
            INSERT INTO cart_items (cart_key, product_id, quantity) VALUES (:key, :product, :quantity)
            ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)
        ')->execute(['key' => $key, 'product' => $productId, 'quantity' => $newQuantity]);

        return $this->cart($key) + ['message' => $product['name'] . ' quedó en el carrito.'];
    }

    public function removeItem(string $key, int $productId): array
    {
        Database::pdo()->prepare('DELETE FROM cart_items WHERE cart_key = :key AND product_id = :product')
            ->execute(['key' => $key, 'product' => $productId]);

        return $this->cart($key);
    }

    public function clear(string $key): array
    {
        Database::pdo()->prepare('DELETE FROM cart_items WHERE cart_key = :key')->execute(['key' => $key]);
        return $this->cart($key);
    }

    public function checkout(string $key, array $input, string $token, ?int $userId): array
    {
        $name = trim((string) ($input['customer_name'] ?? ''));
        $phone = trim((string) ($input['customer_phone'] ?? ''));
        $notes = trim((string) ($input['notes'] ?? ''));
        if ($name === '' || $phone === '') {
            return ['success' => false, 'message' => 'Escribe tu nombre y un teléfono de contacto.'];
        }

        $cart = $this->cart($key);
        if (!($cart['success'] ?? false)) {
            return $cart;
        }
        $items = $cart['data']['items'];
        if (!$items) {
            return ['success' => false, 'message' => 'El carrito está vacío.'];
        }
        foreach ($items as $item) {
            if ($item['problem'] !== null) {
                return ['success' => false, 'message' => $item['name'] . ': ' . $item['problem']];
            }
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $pdo->prepare('
            INSERT INTO orders (user_id, customer_name, customer_phone, notes, total)
            VALUES (:user_id, :name, :phone, :notes, :total)
        ')->execute([
            'user_id' => $userId,
            'name' => mb_substr($name, 0, 120),
            'phone' => mb_substr($phone, 0, 40),
            'notes' => $notes !== '' ? mb_substr($notes, 0, 500) : null,
            'total' => $cart['data']['total'],
        ]);
        $orderId = (int) $pdo->lastInsertId();
        $line = $pdo->prepare('
            INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity)
            VALUES (:order_id, :product_id, :name, :price, :quantity)
        ');
        foreach ($items as $item) {
            $line->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }
        $pdo->commit();

        // El stock lo descuenta el servicio de inventario. Si un producto falla a mitad, se devuelve lo ya descontado.
        $done = [];
        foreach ($items as $item) {
            $response = $this->client->post('inventory', 'movements', [
                'product_id' => $item['product_id'],
                'movement_type' => 'output',
                'quantity' => $item['quantity'],
                'reason' => 'Venta tienda, pedido #' . $orderId,
                'reference' => 'PED-' . $orderId,
            ], $token);

            if (!($response['success'] ?? false)) {
                $this->restock($done, $orderId, $token, 'Reverso pedido #' . $orderId);
                $pdo->prepare('DELETE FROM orders WHERE id = :id')->execute(['id' => $orderId]);
                return ['success' => false, 'message' => $item['name'] . ': ' . ($response['message'] ?? 'no se pudo descontar del inventario.')];
            }
            $done[] = $item;
        }

        $this->clear($key);
        Logger::audit('create', 'orders', $orderId, [
            'customer' => $name,
            'total' => $cart['data']['total'],
            'items' => array_map(fn (array $item) => $item['quantity'] . ' x ' . $item['name'], $items),
        ], $userId);

        return ['success' => true, 'data' => ['order_id' => $orderId, 'total' => $cart['data']['total']]];
    }

    public function orders(array $filters): array
    {
        $sql = 'SELECT orders.*, users.name AS user_name FROM orders LEFT JOIN users ON users.id = orders.user_id';
        $params = [];
        if (in_array($filters['status'] ?? '', ['pending', 'delivered', 'cancelled'], true)) {
            $sql .= ' WHERE orders.status = :status';
            $params['status'] = $filters['status'];
        }

        $stmt = Database::pdo()->prepare($sql . ' ORDER BY orders.id DESC LIMIT 200');
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        if ($orders) {
            $ids = array_column($orders, 'id');
            $lines = Database::pdo()->prepare('SELECT * FROM order_items WHERE order_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY id');
            $lines->execute($ids);
            $byOrder = [];
            foreach ($lines->fetchAll() as $line) {
                $byOrder[$line['order_id']][] = $line;
            }
            foreach ($orders as &$order) {
                $order['items'] = $byOrder[$order['id']] ?? [];
            }
        }

        return ['success' => true, 'data' => $orders];
    }

    public function setStatus(int $orderId, string $status, string $token): array
    {
        if (!in_array($status, ['delivered', 'cancelled'], true)) {
            return ['success' => false, 'message' => 'Estado inválido.'];
        }

        $pdo = Database::pdo();
        // Solo se cambia si sigue pendiente, así dos clics seguidos no devuelven el stock dos veces.
        $update = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id AND status = 'pending'");
        $update->execute(['status' => $status, 'id' => $orderId]);
        if ($update->rowCount() === 0) {
            return ['success' => false, 'message' => 'El pedido no existe o ya fue cerrado.'];
        }

        $warning = null;
        if ($status === 'cancelled') {
            $lines = $pdo->prepare('SELECT product_id, product_name AS name, quantity FROM order_items WHERE order_id = :id');
            $lines->execute(['id' => $orderId]);
            $failed = $this->restock($lines->fetchAll(), $orderId, $token, 'Pedido #' . $orderId . ' cancelado');
            if ($failed) {
                $warning = 'No se pudo devolver al inventario: ' . implode(', ', $failed) . '. Regístralo a mano.';
            }
        }

        Logger::audit($status === 'cancelled' ? 'cancel' : 'deliver', 'orders', $orderId);

        return ['success' => true, 'warning' => $warning];
    }

    private function restock(array $items, int $orderId, string $token, string $reason): array
    {
        $failed = [];
        foreach ($items as $item) {
            $response = $this->client->post('inventory', 'movements', [
                'product_id' => $item['product_id'],
                'movement_type' => 'input',
                'quantity' => $item['quantity'],
                'reason' => $reason,
                'reference' => 'PED-' . $orderId,
            ], $token);
            if (!($response['success'] ?? false)) {
                $failed[] = $item['name'];
            }
        }

        return $failed;
    }

    private function catalog(): array
    {
        $response = $this->client->get('catalog', 'products');
        if (!($response['success'] ?? false)) {
            return ['success' => false, 'message' => 'El catálogo no está disponible en este momento.'];
        }

        $byId = [];
        foreach ($response['data'] as $product) {
            $byId[(int) $product['id']] = $product;
        }

        return $byId;
    }
}
