<?php

class UserController extends CrudController
{
    protected string $modelClass = User::class;
    protected string $viewFolder = 'users';
    protected string $title = 'Usuarios';
    protected array $fillable = ['name', 'email', 'role_id', 'status'];
    protected array $searchable = ['name', 'email'];
    protected array $labels = [
        'name' => 'Nombre',
        'email' => 'Correo',
        'role_id' => 'Rol',
        'status' => 'Estado',
    ];
    protected array $fieldTypes = [
        'role_id' => 'select',
        'status' => 'select',
    ];

    public function index(): void
    {
        AuthMiddleware::handle();
        $items = (new User())->allWithRoles();
        $this->render('users/index', [
            'title' => $this->title,
            'items' => $items,
            'query' => '',
            'labels' => $this->labels,
            'extraColumns' => [],
            'fieldTypes' => $this->fieldTypes,
            'routeBase' => $this->viewFolder,
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        $this->render('users/form', [
            'title' => $this->title,
            'item' => [],
            'action' => $this->viewFolder . '/store',
            'backRoute' => $this->viewFolder . '/index',
            'labels' => $this->labels,
            'selectOptions' => $this->selectOptions(),
            'fields' => array_merge($this->fillable, ['password']),
            'fieldTypes' => ['status' => 'select', 'password' => 'password'],
        ]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $id = (int) ($_GET['id'] ?? 0);
        $item = (new User())->find($id) ?? [];

        $this->render('users/form', [
            'title' => $this->title,
            'item' => $item,
            'action' => $this->viewFolder . '/update&id=' . $id,
            'backRoute' => $this->viewFolder . '/index',
            'labels' => $this->labels,
            'selectOptions' => $this->selectOptions(),
            'fields' => array_merge($this->fillable, ['password']),
            'fieldTypes' => ['status' => 'select', 'password' => 'password'],
        ]);
    }

    protected function prepareStoreData(array $source): array
    {
        $data = $this->filterInput($source);
        $password = trim((string) ($source['password'] ?? ''));
        $data['password_hash'] = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : '';
        unset($data['password']);
        return $data;
    }

    protected function prepareUpdateData(array $source, int $id): array
    {
        $data = $this->filterInput($source);
        if (!empty(trim((string) ($source['password'] ?? '')))) {
            $data['password_hash'] = password_hash(trim((string) $source['password']), PASSWORD_BCRYPT);
        }
        unset($data['password']);
        return $data;
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        $data = $this->prepareStoreData($_POST);
        $errors = Validator::required($data, ['name', 'email', 'role_id', 'status', 'password_hash']);
        if (!Validator::email($data['email'] ?? '')) {
            $errors['email'] = 'Correo inválido.';
        }
        if ($errors) {
            Session::setOld($data);
            Session::setFlash('error', 'Revisa los campos obligatorios.');
            redirect($this->viewFolder . '/create');
        }

        $id = (new User())->create($data);
        Logger::audit('create', 'users', $id, $data);
        Session::setFlash('success', 'Usuario creado correctamente.');
        redirect($this->viewFolder . '/index');
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        $id = (int) ($_GET['id'] ?? 0);
        $data = $this->prepareUpdateData($_POST, $id);
        $errors = Validator::required($data, ['name', 'email', 'role_id', 'status']);
        if (!Validator::email($data['email'] ?? '')) {
            $errors['email'] = 'Correo inválido.';
        }
        if ($errors) {
            Session::setOld($data);
            Session::setFlash('error', 'Revisa los campos obligatorios.');
            redirect($this->viewFolder . '/edit&id=' . $id);
        }

        (new User())->update($id, $data);
        Logger::audit('update', 'users', $id, $data);
        Session::setFlash('success', 'Usuario actualizado correctamente.');
        redirect($this->viewFolder . '/index');
    }

    private function selectOptions(): array
    {
        return [
            'role_id' => array_map(
                fn ($row) => ['value' => $row['id'], 'label' => $row['name']],
                (new Role())->all('name ASC')
            ),
            'status' => [
                ['value' => 'active', 'label' => 'Activo'],
                ['value' => 'inactive', 'label' => 'Inactivo'],
            ],
        ];
    }
}
