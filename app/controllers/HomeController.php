<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'Inicio',
            'categories' => (new Category())->where('status', 'active', 'name ASC'),
            'products' => (new Product())->publicCatalog(),
            'heroStats' => [
                ['label' => 'Categorías', 'value' => (new Category())->count()],
                ['label' => 'Productos', 'value' => (new Product())->count()],
                ['label' => 'Mascotas atendidas', 'value' => (new Animal())->count()],
                ['label' => 'Historiales', 'value' => (new Treatment())->count() + (new Vaccine())->count()],
            ],
        ]);
    }

    public function catalog(): void
    {
        $this->render('home/catalog', [
            'title' => 'Tienda',
            'products' => (new Product())->publicCatalog(),
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
