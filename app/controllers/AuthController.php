<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            redirect('dashboard/index');
        }

        $this->render('auth/login', [
            'title' => 'Iniciar sesión',
        ]);
    }

    public function authenticate(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            Session::setFlash('error', 'Token inválido.');
            redirect('auth/login');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = trim((string) ($_POST['password'] ?? ''));
        $response = (new MicroserviceClient())->post('auth', 'login', [
            'email' => $email,
            'password' => $password,
        ]);

        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'Credenciales incorrectas.');
            redirect('auth/login');
        }

        $user = $response['data']['user'] ?? [];
        Auth::login($user);
        Auth::setToken($response['data']['token'] ?? null);
        Session::setFlash('success', 'Bienvenido, ' . ($user['name'] ?? 'usuario') . '.');
        redirect('dashboard/index');
    }

    public function logout(): void
    {
        if (Auth::check()) {
            Logger::audit('logout', 'auth', Auth::id() ?? 0);
        }
        Auth::logout();
        Session::setFlash('success', 'Sesión cerrada correctamente.');
        redirect('auth/login');
    }
}
