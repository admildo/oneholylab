<?php
declare(strict_types=1);

namespace Holy;

/** Who did what in the admin. */
final class AuditLog
{
    public static function record(string $action, string $entity, ?int $entityId = null, ?string $detail = null): void
    {
        Database::run(
            'INSERT INTO audit_log (user_id, action, entity, entity_id, detail) VALUES (?, ?, ?, ?, ?)',
            [Auth::user()['id'] ?? null, $action, $entity, $entityId, $detail]
        );
    }

    public static function recent(int $limit = 40): array
    {
        return Database::all(
            'SELECT a.*, CONCAT(u.first_name, " ", u.last_name) AS user_name
             FROM audit_log a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT ' . (int) $limit
        );
    }
}
