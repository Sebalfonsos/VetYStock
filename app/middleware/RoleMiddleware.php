<?php

class RoleMiddleware
{
    public static function handle(array $roles): void
    {
        AuthMiddleware::handle();

        if (!in_array(Auth::role(), $roles, true)) {
            Session::setFlash('error', 'No tienes permisos para esta sección.');
            redirect('dashboard/index');
        }
    }
}

