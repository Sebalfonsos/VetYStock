<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $client = new MicroserviceClient();
        $token = Auth::token();
        $usersResponse = $client->get('auth', 'users');
        $rolesResponse = $client->get('auth', 'roles');
        $categoriesResponse = $client->get('catalog', 'categories');
        $productsResponse = $client->get('catalog', 'products');
        $animalsResponse = $client->get('animals', 'animals');
        $inventoryMovementsResponse = $client->get('inventory', 'movements', $token);
        $inventoryAlertsResponse = $client->get('inventory', 'alerts', $token);

        $dashboardCards = [
            ['label' => 'Usuarios', 'value' => count($usersResponse['data'] ?? []), 'icon' => 'bi-people', 'color' => 'primary'],
            ['label' => 'Roles', 'value' => count($rolesResponse['data'] ?? []), 'icon' => 'bi-shield-lock', 'color' => 'dark'],
            ['label' => 'Categorías', 'value' => count($categoriesResponse['data'] ?? []), 'icon' => 'bi-tags', 'color' => 'success'],
            ['label' => 'Productos', 'value' => count($productsResponse['data'] ?? []), 'icon' => 'bi-box-seam', 'color' => 'warning'],
            ['label' => 'Animales', 'value' => count($animalsResponse['data'] ?? []), 'icon' => 'bi-heart-pulse', 'color' => 'danger'],
            ['label' => 'Movimientos', 'value' => count($inventoryMovementsResponse['data'] ?? []), 'icon' => 'bi-journal-medical', 'color' => 'info'],
            ['label' => 'Alertas', 'value' => count($inventoryAlertsResponse['data'] ?? []), 'icon' => 'bi-exclamation-triangle', 'color' => 'secondary'],
        ];

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'cards' => $dashboardCards,
            'recentAnimals' => array_slice($animalsResponse['data'] ?? [], 0, 5),
        ]);
    }
}

