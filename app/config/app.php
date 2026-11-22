<?php

return [
    'name' => 'Laudi Vet & Stock',
    'base_url' => '',
    'timezone' => 'America/Bogota',
    'default_route' => 'home/index',
    'login_route' => 'auth/login',
    'service_secret' => getenv('SERVICE_SECRET') ?: 'change-me-in-production',
    'services' => [
        'auth' => getenv('AUTH_SERVICE_URL') ?: 'http://localhost:8101',
        'catalog' => getenv('CATALOG_SERVICE_URL') ?: 'http://localhost:8102',
        'inventory' => getenv('INVENTORY_SERVICE_URL') ?: 'http://localhost:8103',
        'animals' => getenv('ANIMALS_SERVICE_URL') ?: 'http://localhost:8104',
    ],
];
