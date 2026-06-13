<?php

class Logger
{
    public static function audit(string $action, string $entity, ?int $entityId = null, array $details = []): void
    {
        try {
            $pdo = Database::pdo();
            $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, user_agent, created_at) VALUES (:user_id, :action, :entity, :entity_id, :details, :ip_address, :user_agent, NOW())');
            $stmt->execute([
                'user_id' => Auth::id(),
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            ]);
        } catch (Throwable) {
            // Se ignora para no romper la experiencia de demo.
        }
    }
}

