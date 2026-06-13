<?php

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            Session::setFlash('error', 'Debes iniciar sesión.');
            redirect('auth/login');
        }
    }
}

