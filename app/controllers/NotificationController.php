<?php

class NotificationController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $response = (new MicroserviceClient())->get('notifications', 'notifications', Auth::token());
        if (!($response['success'] ?? false)) {
            Session::setFlash('error', $response['message'] ?? 'El servicio de notificaciones no está disponible.');
        }

        $this->render('notifications/index', [
            'title' => 'Notificaciones',
            'notifications' => $response['data'] ?? [],
            'unread' => (int) ($response['unread'] ?? 0),
        ]);
    }

    public function count(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'unread' => 0]);
            return;
        }

        $token = Auth::token();
        session_write_close();
        $response = (new MicroserviceClient())->get('notifications', 'notifications/count', $token);
        echo json_encode(['success' => (bool) ($response['success'] ?? false), 'unread' => (int) ($response['unread'] ?? 0)]);
    }

    public function read(): void
    {
        $this->guardPost();
        $id = (int) ($_POST['id'] ?? 0);
        (new MicroserviceClient())->post('notifications', 'notifications/' . $id . '/read', [], Auth::token());

        $link = (string) ($_POST['link'] ?? '');
        redirect(preg_match('/^[a-z-]+\/[a-z-]+(&[\w=&-]*)?$/', $link) ? $link : 'notifications/index');
    }

    public function readAll(): void
    {
        $this->guardPost();
        (new MicroserviceClient())->post('notifications', 'notifications/read-all', [], Auth::token());
        Session::setFlash('success', 'Todas las notificaciones quedaron como leídas.');
        redirect('notifications/index');
    }

    public function scan(): void
    {
        $this->guardPost();
        $response = (new MicroserviceClient())->post('notifications', 'scan', [], Auth::token(), 15);

        if ($response['success'] ?? false) {
            $data = $response['data'];
            $message = sprintf('Revisión completa: %d eventos revisados, %d avisos nuevos.', $data['checked'], $data['created']);
            if ($data['errors']) {
                $message .= ' Sin respuesta de: ' . implode('; ', $data['errors']);
            }
            Session::setFlash($data['errors'] ? 'error' : 'success', $message);
        } else {
            Session::setFlash('error', $response['message'] ?? 'El servicio de notificaciones no está disponible.');
        }

        redirect('notifications/index');
    }

    private function guardPost(): void
    {
        AuthMiddleware::handle();
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !Csrf::validate($_POST['_token'] ?? null)) {
            Session::setFlash('error', 'La sesión expiró. Intenta de nuevo.');
            redirect('notifications/index');
        }
    }
}
