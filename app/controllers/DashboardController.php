<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $userModel = new User();
        $roleModel = new Role();
        $categoryModel = new Category();
        $productModel = new Product();
        $animalModel = new Animal();
        $treatmentModel = new Treatment();
        $vaccineModel = new Vaccine();

        $recentAnimals = $animalModel->allWithRelations();
        $dashboardCards = [
            ['label' => 'Usuarios', 'value' => $userModel->count(), 'icon' => 'bi-people', 'color' => 'primary'],
            ['label' => 'Roles', 'value' => $roleModel->count(), 'icon' => 'bi-shield-lock', 'color' => 'dark'],
            ['label' => 'Categorías', 'value' => $categoryModel->count(), 'icon' => 'bi-tags', 'color' => 'success'],
            ['label' => 'Productos', 'value' => $productModel->count(), 'icon' => 'bi-box-seam', 'color' => 'warning'],
            ['label' => 'Animales', 'value' => $animalModel->count(), 'icon' => 'bi-heart-pulse', 'color' => 'danger'],
            ['label' => 'Tratamientos', 'value' => $treatmentModel->count(), 'icon' => 'bi-journal-medical', 'color' => 'info'],
            ['label' => 'Vacunas', 'value' => $vaccineModel->count(), 'icon' => 'bi-virus', 'color' => 'secondary'],
        ];

        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'cards' => $dashboardCards,
            'recentAnimals' => array_slice($recentAnimals, 0, 5),
        ]);
    }
}

