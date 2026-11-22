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
    ],
    'llm' => [
        'base_url' => getenv('LLM_BASE_URL') ?: 'http://localhost:1234/v1',
        'model' => getenv('LLM_MODEL') ?: 'qwen2.5-7b-instruct',
        'timeout' => (float) (getenv('LLM_TIMEOUT') ?: 120),
    ],
];
