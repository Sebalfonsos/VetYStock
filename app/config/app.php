<?php

return [
    'name' => 'Laudi Vet & Stock',
    'base_url' => '',
    'timezone' => 'America/Bogota',
    'default_route' => 'home/index',
    'login_route' => 'auth/login',
    'service_secret' => 'change-me-in-production',
    'services' => [
        'auth' => 'http://localhost:8101',
        'catalog' => 'http://localhost:8102',
        'inventory' => 'http://localhost:8103',
        'animals' => 'http://localhost:8104',
    ],
];
