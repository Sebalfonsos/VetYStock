<?php

class AssistantController extends Controller
{
    public function chat(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            return;
        }

        if (!Csrf::validate($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'La sesión expiró. Recarga la página.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $token = Auth::check() ? Auth::token() : null;
        // El modelo puede tardar; sin esto la sesión queda bloqueada y el usuario no puede navegar mientras espera.
        session_write_close();

        $input = json_decode((string) file_get_contents('php://input'), true);
        $response = (new MicroserviceClient())->post(
            'assistant',
            'chat',
            ['messages' => is_array($input['messages'] ?? null) ? $input['messages'] : []],
            $token,
            (float) config('app.llm.timeout', 120) + 10
        );
        unset($response['_status']);

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
