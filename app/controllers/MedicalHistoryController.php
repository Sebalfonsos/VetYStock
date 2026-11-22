<?php

class MedicalHistoryController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $filters = [
            'animal_id' => (int) ($_GET['animal_id'] ?? 0),
            'from' => trim((string) ($_GET['from'] ?? '')),
            'to' => trim((string) ($_GET['to'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
        ];

        $client = new MicroserviceClient();
        $animals = $client->get('animals', 'animals')['data'] ?? [];
        $events = [];
        $serviceError = null;

        if ($filters['animal_id'] > 0) {
            $response = $client->get('health', 'history', Auth::token(), $filters);
            if ($response['success'] ?? false) {
                $events = $response['data'];
            } else {
                $serviceError = $response['message'] ?? 'El servicio de salud no está disponible.';
            }
        }

        $this->render('medical-history/index', [
            'title' => 'Historial médico',
            'animals' => $animals,
            'filters' => $filters,
            'events' => $events,
            'serviceError' => $serviceError,
        ]);
    }
}
