<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $catalogClient = new MicroserviceClient();
        $categoriesResponse = $catalogClient->get('catalog', 'categories');
        $productsResponse = $catalogClient->get('catalog', 'products');
        $authClient = new MicroserviceClient();
        $animalsClient = new MicroserviceClient();
        $usersResponse = $authClient->get('auth', 'users');
        $animalsResponse = $animalsClient->get('animals', 'animals');

        $this->render('home/index', [
            'title' => 'Inicio',
            'categories' => $categoriesResponse['data'] ?? [],
            'products' => $productsResponse['data'] ?? [],
            'heroStats' => [
                ['label' => 'Categorías', 'value' => count($categoriesResponse['data'] ?? [])],
                ['label' => 'Productos', 'value' => count($productsResponse['data'] ?? [])],
                ['label' => 'Usuarios', 'value' => count($usersResponse['data'] ?? [])],
                ['label' => 'Mascotas', 'value' => count($animalsResponse['data'] ?? [])],
            ],
        ]);
    }

    public function catalog(): void
    {
        $productsResponse = (new MicroserviceClient())->get('catalog', 'products');
        $this->render('home/catalog', [
            'title' => 'Tienda',
            'products' => $productsResponse['data'] ?? [],
        ]);
    }

    public function about(): void
    {
        $this->render('home/about', [
            'title' => 'Nosotros',
        ]);
    }

    public function contact(): void
    {
        $this->render('home/contact', [
            'title' => 'Contacto',
        ]);
    }
}
