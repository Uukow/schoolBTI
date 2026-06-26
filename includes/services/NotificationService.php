<?php
/**
 * Notification Service
 * Creates in-app notifications and triggers email/SMS when configured
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

class NotificationService
{
    /**
     * Send notification to a user
     */
    public static function send($options)
    {
        $userId = $options['user_id'] ?? null;
        $title = $options['title'] ?? 'Notification';
        $message = $options['message'] ?? '';
        $type = $options['type'] ?? 'general';
        $channels = $options['channels'] ?? ['in_app'];

        if (!$userId) {
            return false;
        }

        if (in_array('in_app', $channels, true)) {
            self::createInAppNotification($userId, $title, $message, $type);
        }

        return true;
    }

    /**
     * Insert into notifications table
     */
    private static function createInAppNotification($userId, $title, $message, $type)
    {
        $checkTable = executeQuery("SHOW TABLES LIKE 'notifications'");
        if (!$checkTable || !fetchOne($checkTable)) {
            return false;
        }

        $sql = "INSERT INTO notifications (user_id, title, message, notification_type, is_read, created_at)
                VALUES (?, ?, ?, ?, 0, NOW())";
        return executeQuery($sql, 'isss', [$userId, $title, $message, $type]);
    }

    /**
     * Notify HR admins in a branch about an event
     */
    public static function notifyHrAdmins($branchId, $title, $message, $type = 'hr')
    {
        $sql = "SELECT u.id FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                WHERE u.is_active = 1 AND r.role_name IN ('Super Admin', 'Admin')
                AND (u.branch_id = ? OR ? IS NULL OR EXISTS (
                    SELECT 1 FROM roles r2 WHERE r2.id = u.role_id AND r2.role_name = 'Super Admin'
                ))";
        $admins = fetchAll(executeQuery($sql, 'ii', [$branchId, $branchId]));

        foreach ($admins as $admin) {
            self::send([
                'user_id' => $admin['id'],
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'channels' => ['in_app'],
            ]);
        }
    }
}
