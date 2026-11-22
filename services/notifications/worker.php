<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/services/ServiceRuntime.php';
ServiceRuntime::bootstrap();

$interval = max(10, (int) (getenv('NOTIFICATIONS_INTERVAL') ?: 300));
$service = new NotificationService();

echo "[notifications-worker] revisando cada {$interval} s\n";

while (true) {
    $wait = $interval;
    try {
        $result = $service->scan()['data'];
        printf(
            "[%s] revisados %d eventos, %d avisos nuevos%s\n",
            date('Y-m-d H:i:s'),
            $result['checked'],
            $result['created'],
            $result['errors'] ? ' · errores: ' . implode('; ', $result['errors']) : ''
        );
        // Al arrancar, los otros servicios pueden tardar unos segundos; no tiene sentido esperar el intervalo completo.
        if ($result['errors']) {
            $wait = min($interval, 30);
        }
    } catch (Throwable $exception) {
        printf("[%s] error: %s\n", date('Y-m-d H:i:s'), $exception->getMessage());
        $wait = min($interval, 30);
    }

    sleep($wait);
}
