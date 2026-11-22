<?php

class Logger
{
    public static function audit(string $action, string $entity, ?int $entityId = null, array $details = [], ?int $userId = null): void
    {
        $userId ??= Auth::id() ?? self::userFromToken();

        $response = (new MicroserviceClient())->post('reports', 'audit', [
            'user_id' => $userId ?: null,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI'), 0, 255),
        ], ServiceRuntime::systemToken('logger'), 2);

        // La bitácora no debe romper la operación principal si el servicio de reportes está apagado.
        if (!($response['success'] ?? false)) {
            error_log('Auditoría no registrada (' . $action . ' ' . $entity . '): ' . ($response['message'] ?? 'sin respuesta'));
        }
    }

    private static function userFromToken(): ?int
    {
        $sub = (int) (ServiceRuntime::claims()['sub'] ?? 0);
        return $sub > 0 ? $sub : null;
    }
}
