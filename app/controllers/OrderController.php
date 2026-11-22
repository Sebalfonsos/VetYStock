<?php

class OrderController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $status = (string) ($_GET['status'] ?? 'pending');
        $response = (new MicroserviceClient())->get('cart', 'orders', Auth::token(), $status !== '' ? ['status' => $status] : []);
        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'El servicio de carrito no está disponible.');
        }

        $this->render('orders/index', [
            'title' => 'Pedidos',
            'orders' => $response['data'] ?? [],
            'status' => $status,
        ]);
    }

    public function status(): void
    {
        AuthMiddleware::handle();
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !Csrf::validate($_POST['_token'] ?? null)) {
            Session::setFlash('error', 'La sesión expiró. Intenta de nuevo.');
            redirect('orders/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');
        $response = (new MicroserviceClient())->post('cart', 'orders/' . $id . '/status', ['status' => $status], Auth::token(), 20);

        if ($response['success'] ?? false) {
            Session::setFlash('success', 'Pedido #' . $id . ($status === 'cancelled' ? ' cancelado; los productos volvieron al inventario.' : ' marcado como entregado.'));
            if (!empty($response['warning'])) {
                Session::setFlash('error', $response['warning']);
            }
        } else {
            Session::setFlash('error', $response['message'] ?? 'No se pudo actualizar el pedido.');
        }

        redirect('orders/index');
    }
}
