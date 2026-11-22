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
        'assistant' => getenv('ASSISTANT_SERVICE_URL') ?: 'http://localhost:8105',
        'health' => getenv('HEALTH_SERVICE_URL') ?: 'http://localhost:8106',
        'notifications' => getenv('NOTIFICATIONS_SERVICE_URL') ?: 'http://localhost:8107',
        'reports' => getenv('REPORTS_SERVICE_URL') ?: 'http://localhost:8108',
        'cart' => getenv('CART_SERVICE_URL') ?: 'http://localhost:8109',
    ],
    'llm' => [
        'base_url' => getenv('LLM_BASE_URL') ?: 'http://localhost:1234/v1',
        'model' => getenv('LLM_MODEL') ?: 'openai/gpt-oss-20b',
        'timeout' => (float) (getenv('LLM_TIMEOUT') ?: 180),
    ],
];
