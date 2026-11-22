<?php

abstract class HealthResource
{
    abstract protected function resource(): string;

    private MicroserviceClient $client;

    public function __construct()
    {
        $this->client = new MicroserviceClient();
    }

    public function all(string $orderBy = ''): array
    {
        return $this->expect($this->client->get('health', $this->resource(), Auth::token()))['data'] ?? [];
    }

    public function search(array $columns, string $term): array
    {
        return $this->expect($this->client->get('health', $this->resource(), Auth::token(), ['q' => $term]))['data'] ?? [];
    }

    public function find(int $id): ?array
    {
        $response = $this->client->get('health', $this->resource() . '/' . $id, Auth::token());
        return ($response['success'] ?? false) ? $response['data'] : null;
    }

    public function create(array $data): int
    {
        return (int) ($this->expect($this->client->post('health', $this->resource(), $data, Auth::token()))['data']['id'] ?? 0);
    }

    public function update(int $id, array $data): bool
    {
        $this->expect($this->client->put('health', $this->resource() . '/' . $id, $data, Auth::token()));
        return true;
    }

    public function delete(int $id): bool
    {
        $this->expect($this->client->delete('health', $this->resource() . '/' . $id, Auth::token()));
        return true;
    }

    public function hasColumn(string $column): bool
    {
        return false;
    }

    private function expect(array $response): array
    {
        if (!($response['success'] ?? false)) {
            throw new ServiceException($response['message'] ?? 'El servicio de salud no está disponible.');
        }

        return $response;
    }
}
