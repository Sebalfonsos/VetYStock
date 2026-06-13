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
        $user = (new User())->authenticate($email, $password);

        if (!$user) {
            Session::setFlash('error', 'Credenciales incorrectas.');
            redirect('auth/login');
        }

        (new User())->update((int) $user['id'], [
            'role_id' => $user['role_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'status' => $user['status'],
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::login($user);
        Logger::audit('login', 'auth', $user['id'], ['email' => $email]);
        Session::setFlash('success', 'Bienvenido, ' . $user['name'] . '.');
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
