<?php

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user'] = $user;
    }

    public static function setToken(?string $token): void
    {
        if ($token === null || $token === '') {
            unset($_SESSION['service_token']);
            return;
        }

        $_SESSION['service_token'] = $token;
    }

    public static function token(): ?string
    {
        return $_SESSION['service_token'] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['service_token']);
    }

    public static function role(): string
    {
        return self::user()['role_name'] ?? 'Invitado';
    }
}

