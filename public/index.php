<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/functions.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/services/' . $class . '.php',
        __DIR__ . '/../app/middleware/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Session::start();
date_default_timezone_set((string) config('app.timezone', 'America/Bogota'));

$route = trim((string) ($_GET['route'] ?? config('app.default_route')), '/');
[$controllerSlug, $action] = array_pad(explode('/', $route, 2), 2, 'index');

$controllerMap = [
    'auth' => AuthController::class,
    'dashboard' => DashboardController::class,
    'home' => HomeController::class,
    'api' => ApiController::class,
    'users' => UserController::class,
    'roles' => RoleController::class,
    'categories' => CategoryController::class,
    'products' => ProductController::class,
    'inventory' => InventoryController::class,
    'animals' => AnimalController::class,
    'treatments' => TreatmentController::class,
    'vaccines' => VaccineController::class,
    'observations' => ObservationController::class,
    'medical-history' => MedicalHistoryController::class,
];

$controllerClass = $controllerMap[$controllerSlug] ?? (studly($controllerSlug) . 'Controller');

if (!class_exists($controllerClass)) {
    http_response_code(404);
    echo 'Ruta no encontrada';
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    echo 'Acción no encontrada';
    exit;
}

if (!Auth::check() && $route !== config('app.login_route') && $route !== 'auth/authenticate') {
    $publicRoutes = ['home/index', 'home/catalog', 'home/contact', 'home/about', 'api/products', 'auth/login', 'auth/authenticate'];
    if (!in_array($route, $publicRoutes, true)) {
        redirect('auth/login');
    }
}

$controller->$action();
