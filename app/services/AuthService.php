<?php

class AuthService
{
    public function login(string $email, string $password): array
    {
        $user = (new User())->authenticate($email, $password);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ];
        }

        $updateData = [
            'role_id' => $user['role_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'password_hash' => $user['password_hash'],
            'status' => $user['status'],
        ];

        $userModel = new User();
        if ($userModel->hasColumn('last_login_at')) {
            $updateData['last_login_at'] = date('Y-m-d H:i:s');
        }

        $userModel->update((int) $user['id'], $updateData);

        $token = ServiceToken::issue([
            'sub' => (int) $user['id'],
            'role' => $user['role_name'] ?? null,
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        Logger::audit('login', 'auth', (int) $user['id'], ['email' => $email]);

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => (int) $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role_name'] ?? null,
                ],
            ],
        ];
    }

    public function me(string $token): array
    {
        $claims = ServiceToken::validate($token);
        if (!$claims) {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado.',
            ];
        }

        return [
            'success' => true,
            'data' => $claims,
        ];
    }

    public function roles(): array
    {
        return [
            'success' => true,
            'data' => (new Role())->all('name ASC'),
        ];
    }

    public function users(): array
    {
        return [
            'success' => true,
            'data' => (new User())->allWithRoles(),
        ];
    }

    public function createUser(array $input): array
    {
        $data = $this->prepareUserData($input, true);
        $errors = Validator::required($data, ['name', 'email', 'role_id', 'status', 'password_hash']);
        if (!Validator::email($data['email'] ?? '')) {
            $errors['email'] = 'Correo inválido.';
        }

        if ($errors) {
            return [
                'success' => false,
                'message' => 'Revisa los campos obligatorios.',
                'errors' => $errors,
            ];
        }

        $id = (new User())->create($data);
        Logger::audit('create', 'users', $id, $data);

        return [
            'success' => true,
            'data' => ['id' => $id],
        ];
    }

    public function updateUser(int $id, array $input): array
    {
        $data = $this->prepareUserData($input, false);
        $errors = Validator::required($data, ['name', 'email', 'role_id', 'status']);
        if (!Validator::email($data['email'] ?? '')) {
            $errors['email'] = 'Correo inválido.';
        }

        if ($errors) {
            return [
                'success' => false,
                'message' => 'Revisa los campos obligatorios.',
                'errors' => $errors,
            ];
        }

        (new User())->update($id, $data);
        Logger::audit('update', 'users', $id, $data);

        return [
            'success' => true,
            'data' => ['id' => $id],
        ];
    }

    public function deleteUser(int $id): array
    {
        (new User())->delete($id);
        Logger::audit('delete', 'users', $id);

        return [
            'success' => true,
        ];
    }

    private function prepareUserData(array $input, bool $includePassword): array
    {
        $data = [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'role_id' => (int) ($input['role_id'] ?? 0),
            'status' => trim((string) ($input['status'] ?? '')),
        ];

        if ($includePassword) {
            $password = trim((string) ($input['password'] ?? ''));
            $data['password_hash'] = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : '';
        } elseif (!empty(trim((string) ($input['password'] ?? '')))) {
            $data['password_hash'] = password_hash(trim((string) $input['password']), PASSWORD_BCRYPT);
        }

        return $data;
    }
}
