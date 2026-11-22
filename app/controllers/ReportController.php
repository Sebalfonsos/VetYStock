<?php

class ReportController extends Controller
{
    private const TYPES = [
        'inventory' => ['Inventario actual', ['category']],
        'movements' => ['Movimientos de inventario', ['from', 'to', 'movement_type']],
        'animal-history' => ['Historial por animal', ['animal_id', 'from', 'to', 'type']],
        'consultations' => ['Consultas al asistente', ['from', 'to']],
        'audit' => ['Bitácora del sistema', ['from', 'to', 'user_id', 'entity', 'action']],
    ];

    public function index(): void
    {
        RoleMiddleware::handle(['Administrador']);

        $type = array_key_exists($_GET['type'] ?? '', self::TYPES) ? $_GET['type'] : 'inventory';
        [$label, $filterKeys] = self::TYPES[$type];
        $filters = [];
        foreach ($filterKeys as $key) {
            $filters[$key] = trim((string) ($_GET[$key] ?? ''));
        }

        $client = new MicroserviceClient();
        $token = Auth::token();
        $response = $client->get('reports', 'reports/' . $type, $token, array_filter($filters, fn ($value) => $value !== ''));
        $report = ($response['success'] ?? false) ? $response['data'] : null;

        if ($report && ($_GET['export'] ?? '') === 'csv') {
            $this->csv($type, $report);
        }

        $this->render('reports/index', [
            'title' => 'Reportes',
            'types' => array_map(fn ($config) => $config[0], self::TYPES),
            'type' => $type,
            'label' => $label,
            'filters' => $filters,
            'report' => $report,
            'serviceError' => $report ? null : ($response['message'] ?? 'El servicio de reportes no está disponible.'),
            'options' => $this->options($type, $client, $token),
        ]);
    }

    private function options(string $type, MicroserviceClient $client, ?string $token): array
    {
        return match ($type) {
            'inventory' => ['category' => $client->get('catalog', 'categories')['data'] ?? []],
            'animal-history' => ['animal_id' => $client->get('animals', 'animals')['data'] ?? []],
            'audit' => ['user_id' => $client->get('auth', 'users', $token)['data'] ?? []],
            default => [],
        };
    }

    private function csv(string $type, array $report): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte-' . $type . '-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, array_values($report['columns']), ';');
        foreach ($report['rows'] as $row) {
            fputcsv($output, array_map(
                fn ($key) => $this->csvSafe($row[$key] ?? ''),
                array_keys($report['columns'])
            ), ';');
        }
        fclose($output);
        exit;
    }

    // Evita que Excel interprete como fórmula un texto que empiece con =, +, - o @.
    private function csvSafe(mixed $value): string
    {
        $value = (string) $value;
        return $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true) && !is_numeric($value) ? "'" . $value : $value;
    }
}
