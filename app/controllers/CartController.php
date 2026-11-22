<?php

class CartController extends Controller
{
    public function index(): void
    {
        $response = $this->client()->get('cart', 'cart/' . $this->key(), $this->token());
        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'El carrito no está disponible en este momento.');
        }

        $this->render('cart/index', [
            'title' => 'Carrito',
            'cart' => $response['data'] ?? ['items' => [], 'count' => 0, 'total' => 0],
            'lastOrder' => $this->pull('last_order'),
        ]);
    }

    // Resumen en JSON para el contador del menú.
    public function items(): void
    {
        $key = $this->key();
        $token = $this->token();
        session_write_close();
        $this->json($this->client()->get('cart', 'cart/' . $key, $token));
    }

    // Lo usan la tienda y el chat: agrega sin recargar la página.
    public function add(): void
    {
        $this->guardJson();
        $input = json_decode((string) file_get_contents('php://input'), true);
        $items = is_array($input['items'] ?? null) ? $input['items'] : [$input];

        $key = $this->key();
        $token = $this->token();
        session_write_close();

        $added = [];
        $errors = [];
        $response = null;
        foreach (array_slice($items, 0, 20) as $item) {
            $response = $this->client()->post('cart', 'cart/' . $key . '/items', [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
            ], $token);
            if ($response['success'] ?? false) {
                $added[] = (int) $item['product_id'];
            } else {
                $errors[] = $response['message'] ?? 'No se pudo agregar.';
            }
        }

        if (!$added) {
            $this->json(['success' => false, 'message' => $errors[0] ?? 'No se pudo agregar al carrito.'], 422);
        }

        $cart = $this->client()->get('cart', 'cart/' . $key, $token);
        $this->json([
            'success' => true,
            'added' => $added,
            'message' => count($added) === 1 ? ($response['message'] ?? 'Producto agregado.') : count($added) . ' productos agregados al carrito.',
            'errors' => $errors,
            'count' => (int) ($cart['data']['count'] ?? 0),
        ]);
    }

    public function update(): void
    {
        $this->guardForm();
        $response = $this->client()->put('cart', 'cart/' . $this->key() . '/items', [
            'product_id' => (int) ($_POST['product_id'] ?? 0),
            'quantity' => max(0, (int) ($_POST['quantity'] ?? 0)),
        ], $this->token());
        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'No se pudo actualizar el carrito.');
        }
        redirect('cart/index');
    }

    public function remove(): void
    {
        $this->guardForm();
        $this->client()->delete('cart', 'cart/' . $this->key() . '/items/' . (int) ($_POST['product_id'] ?? 0), $this->token());
        redirect('cart/index');
    }

    public function clear(): void
    {
        $this->guardForm();
        $this->client()->delete('cart', 'cart/' . $this->key(), $this->token());
        redirect('cart/index');
    }

    public function checkout(): void
    {
        $this->guardForm();
        $input = [
            'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
            'customer_phone' => trim((string) ($_POST['customer_phone'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];

        $response = $this->client()->post('cart', 'cart/' . $this->key() . '/checkout', $input, $this->token(), 20);
        if ($response['success'] ?? false) {
            Session::clearOld();
            $_SESSION['last_order'] = $response['data'];
        } else {
            Session::setOld($input);
            Session::setFlash('error', $response['message'] ?? 'No se pudo registrar el pedido.');
        }
        redirect('cart/index');
    }

    private function key(): string
    {
        if (!preg_match('/^[a-f0-9]{32}$/', (string) ($_SESSION['cart_key'] ?? ''))) {
            $_SESSION['cart_key'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['cart_key'];
    }

    // Con sesión se usa el token del usuario (queda como responsable del pedido); sin ella, uno de sistema.
    private function token(): string
    {
        return Auth::check() ? (string) Auth::token() : ServiceRuntime::systemToken('main');
    }

    private function client(): MicroserviceClient
    {
        return new MicroserviceClient();
    }

    private function pull(string $key): mixed
    {
        $value = $_SESSION[$key] ?? null;
        unset($_SESSION[$key]);
        return $value;
    }

    private function guardJson(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido.'], 405);
        }
        if (!Csrf::validate($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            $this->json(['success' => false, 'message' => 'La sesión expiró. Recarga la página.'], 419);
        }
    }

    private function guardForm(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !Csrf::validate($_POST['_token'] ?? null)) {
            Session::setFlash('error', 'La sesión expiró. Intenta de nuevo.');
            redirect('cart/index');
        }
    }

    private function json(array $payload, int $status = 200): never
    {
        unset($payload['_status']);
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
