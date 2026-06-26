<?php
/**
 * HR Audit Service
 * Module-specific audit logging for sensitive HR operations
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class HrAuditService
{
    public static function log($action, $entityType, $entityId = null, $oldValues = null, $newValues = null, $userId = null)
    {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $oldJson = $oldValues ? json_encode($oldValues) : null;
        $newJson = $newValues ? json_encode($newValues) : null;

        $sql = "INSERT INTO hr_audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        executeQuery($sql, 'ississs', [
            $userId, $action, $entityType, $entityId, $oldJson, $newJson, $ip
        ]);
    }
}
