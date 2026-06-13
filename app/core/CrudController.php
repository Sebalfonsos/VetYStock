<?php

abstract class CrudController extends Controller
{
    protected string $modelClass;
    protected string $viewFolder;
    protected string $title;
    protected array $fillable = [];
    protected array $searchable = [];
    protected array $labels = [];
    protected array $selectOptions = [];
    protected array $fieldTypes = [];
    protected array $extraColumns = [];
    protected string $orderBy = 'id DESC';

    public function index(): void
    {
        AuthMiddleware::handle();
        $model = new $this->modelClass();
        $query = trim((string) ($_GET['q'] ?? ''));
        $items = $query !== '' && $this->searchable
            ? $model->search($this->searchable, $query)
            : $model->all($this->orderBy);

        $this->render($this->viewFolder . '/index', [
            'title' => $this->title,
            'items' => $items,
            'query' => $query,
            'labels' => $this->labels,
            'extraColumns' => $this->extraColumns,
            'fieldTypes' => $this->fieldTypes,
            'routeBase' => $this->viewFolder,
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::handle();
        $this->render($this->viewFolder . '/form', [
            'title' => $this->title,
            'item' => [],
            'action' => $this->viewFolder . '/store',
            'labels' => $this->labels,
            'selectOptions' => $this->selectOptions,
            'fields' => $this->fillable,
            'fieldTypes' => $this->fieldTypes,
            'routeBase' => $this->viewFolder,
            'backRoute' => $this->viewFolder . '/index',
        ]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new $this->modelClass();
        $item = $model->find($id) ?? [];

        $this->render($this->viewFolder . '/form', [
            'title' => $this->title,
            'item' => $item,
            'action' => $this->viewFolder . '/update&id=' . $id,
            'labels' => $this->labels,
            'selectOptions' => $this->selectOptions,
            'fields' => $this->fillable,
            'fieldTypes' => $this->fieldTypes,
            'routeBase' => $this->viewFolder,
            'backRoute' => $this->viewFolder . '/index',
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        $data = $this->prepareStoreData($_POST);
        $errors = Validator::required($data, $this->fillable);
        if ($errors) {
            Session::setOld($data);
            Session::setFlash('error', 'Revisa los campos obligatorios.');
            redirect($this->viewFolder . '/create');
        }

        $id = (new $this->modelClass())->create($data);
        Logger::audit('create', $this->viewFolder, $id, $data);
        Session::clearOld();
        Session::setFlash('success', 'Registro creado correctamente.');
        redirect($this->viewFolder . '/index');
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        $id = (int) ($_GET['id'] ?? 0);
        $data = $this->prepareUpdateData($_POST, $id);
        $errors = Validator::required($data, $this->fillable);
        if ($errors) {
            Session::setOld($data);
            Session::setFlash('error', 'Revisa los campos obligatorios.');
            redirect($this->viewFolder . '/edit&id=' . $id);
        }

        (new $this->modelClass())->update($id, $data);
        Logger::audit('update', $this->viewFolder, $id, $data);
        Session::clearOld();
        Session::setFlash('success', 'Registro actualizado correctamente.');
        redirect($this->viewFolder . '/index');
    }

    public function destroy(): void
    {
        AuthMiddleware::handle();
        $id = (int) ($_GET['id'] ?? 0);
        $model = new $this->modelClass();

        if ($model->hasColumn('status')) {
            $model->update($id, ['status' => 'inactive']);
            Logger::audit('deactivate', $this->viewFolder, $id, ['status' => 'inactive']);
            Session::setFlash('success', 'Registro desactivado correctamente.');
        } else {
            $model->delete($id);
            Logger::audit('delete', $this->viewFolder, $id);
            Session::setFlash('success', 'Registro eliminado correctamente.');
        }
        redirect($this->viewFolder . '/index');
    }

    protected function filterInput(array $source): array
    {
        $data = [];
        foreach ($this->fillable as $field) {
            $data[$field] = trim((string) ($source[$field] ?? ''));
        }
        return $data;
    }

    protected function prepareStoreData(array $source): array
    {
        return $this->filterInput($source);
    }

    protected function prepareUpdateData(array $source, int $id): array
    {
        return $this->filterInput($source);
    }
}
