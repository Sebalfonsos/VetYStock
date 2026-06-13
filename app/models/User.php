<?php

class User extends BaseModel
{
    protected function table(): string
    {
        return 'users';
    }

    public function authenticate(string $email, string $password): ?array
    {
        $stmt = $this->pdo->prepare('SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON roles.id = users.role_id WHERE users.email = :email AND users.status = "active" LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    public function allWithRoles(): array
    {
        return $this->pdo->query('SELECT users.*, roles.name AS role_name FROM users INNER JOIN roles ON roles.id = users.role_id ORDER BY users.id DESC')->fetchAll();
    }
}

