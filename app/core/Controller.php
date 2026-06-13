<?php

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/layouts/main.php';
    }
}

