<?php

class AssistantController extends Controller
{
    public function chat(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->fail(405, 'Método no permitido.');
        }

        if (!Csrf::validate($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
            $this->fail(419, 'La sesión expiró. Recarga la página.');
        }

        $token = Auth::check() ? Auth::token() : null;
        // El modelo puede tardar; sin esto la sesión queda bloqueada y el usuario no puede navegar mientras espera.
        session_write_close();

        $input = json_decode((string) file_get_contents('php://input'), true);
        ServiceRuntime::startStream();

        // Se reenvía tal cual lo que manda el servicio, sin esperar a que termine.
        $error = (new MicroserviceClient())->stream(
            'assistant',
            'chat/stream',
            ['messages' => is_array($input['messages'] ?? null) ? $input['messages'] : []],
            $token,
            (float) config('app.llm.timeout', 180) + 10,
            function (string $chunk): void {
                echo $chunk;
                flush();
            }
        );

        if ($error !== null) {
            ServiceRuntime::streamEvent(['type' => 'error', 'message' => $error]);
        }
    }

    private function fail(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
