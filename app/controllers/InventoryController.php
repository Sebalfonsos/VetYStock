<?php

class InventoryController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $client = new MicroserviceClient();
        $token = Auth::token();
        $movementsResponse = $client->get('inventory', 'movements', $token);
        $alertsResponse = $client->get('inventory', 'alerts', $token);
        $productsResponse = $client->get('catalog', 'products');
        $serviceErrors = [];
        if (!($movementsResponse['success'] ?? false)) {
            $serviceErrors[] = $movementsResponse['message'] ?? 'Inventario no disponible.';
        }
        if (!($alertsResponse['success'] ?? false)) {
            $serviceErrors[] = $alertsResponse['message'] ?? 'Alertas no disponibles.';
        }
        if (!($productsResponse['success'] ?? false)) {
            $serviceErrors[] = $productsResponse['message'] ?? 'Catálogo no disponible.';
        }

        $this->render('inventory/index', [
            'title' => 'Inventario',
            'items' => $movementsResponse['data'] ?? [],
            'products' => $productsResponse['data'] ?? [],
            'alerts' => $alertsResponse['data'] ?? [],
            'serviceError' => $serviceErrors ? implode(' ', $serviceErrors) : null,
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::handle();

        $response = (new MicroserviceClient())->post('inventory', 'movements', [
            'product_id' => (int) ($_POST['product_id'] ?? 0),
            'movement_type' => trim((string) ($_POST['movement_type'] ?? '')),
            'quantity' => max(1, (int) ($_POST['quantity'] ?? 1)),
            'reason' => trim((string) ($_POST['reason'] ?? '')),
            'reference' => trim((string) ($_POST['reference'] ?? '')),
        ], Auth::token());

        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'No se pudo registrar el movimiento.');
            redirect('inventory/index');
        }

        Session::setFlash('success', 'Movimiento registrado correctamente.');
        redirect('inventory/index');
    }
}
