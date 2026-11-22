<?php

class MicroserviceClient
{
    public function get(string $service, string $route, ?string $token = null, array $query = []): array
    {
        return $this->request('GET', $service, $route, null, $token, null, $query);
    }

    public function post(string $service, string $route, array $payload = [], ?string $token = null, ?float $timeout = null): array
    {
        return $this->request('POST', $service, $route, $payload, $token, $timeout);
    }

    public function put(string $service, string $route, array $payload = [], ?string $token = null): array
    {
        return $this->request('PUT', $service, $route, $payload, $token);
    }

    public function delete(string $service, string $route, ?string $token = null): array
    {
        return $this->request('DELETE', $service, $route, null, $token);
    }

    private function request(string $method, string $service, string $route, ?array $payload, ?string $token, ?float $timeout = null, array $query = []): array
    {
        $baseUrl = rtrim((string) config('app.services.' . $service, ''), '/');
        if ($baseUrl === '') {
            return ['success' => false, 'message' => 'Servicio no configurado.'];
        }

        $url = $baseUrl . '/?' . http_build_query(['route' => $route] + $query);
        $headers = ['Accept: application/json'];
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $this->buildHeaders($headers, $token)),
                'ignore_errors' => true,
            ],
        ];

        if ($timeout !== null) {
            $options['http']['timeout'] = $timeout;
        }

        if ($payload !== null) {
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $options['http']['header'] .= "\r\nContent-Type: application/json";
            $options['http']['content'] = $body ?: '{}';
        }

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        $status = 0;

        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $headerLine) {
                if (preg_match('/^HTTP\/\S+\s+(\d+)/', $headerLine, $matches)) {
                    $status = (int) $matches[1];
                    break;
                }
            }
        }

        if ($response === false) {
            return ['success' => false, 'message' => 'No se pudo conectar con ' . $service . '.'];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Respuesta inválida de ' . $service . '.'];
        }

        $decoded['_status'] = $status;
        return $decoded;
    }

    private function buildHeaders(array $headers, ?string $token): array
    {
        if ($token !== null && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        // Para que la bitácora de cada servicio registre al usuario real y no al contenedor que hace la llamada.
        $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if ($clientIp !== null) {
            $headers[] = 'X-Forwarded-For: ' . preg_replace('/[^0-9a-fA-F.:, ]/', '', (string) $clientIp);
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $headers[] = 'User-Agent: ' . str_replace(["\r", "\n"], '', (string) $_SERVER['HTTP_USER_AGENT']);
        }

        return $headers;
    }
}
