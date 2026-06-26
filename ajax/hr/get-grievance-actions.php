<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }

    $grievanceId = (int)($_GET['grievance_id'] ?? 0);
    if (!$grievanceId) {
        jsonResponse(false, 'Grievance ID required');
    }

    $actions = fetchAll(executeQuery(
        "SELECT a.*, u.username,
         CONCAT(s.first_name, ' ', s.last_name) AS action_by_name
         FROM hr_grievance_actions a
         LEFT JOIN users u ON a.action_by = u.id
         LEFT JOIN staff s ON s.user_id = u.id
         WHERE a.grievance_id = ?
         ORDER BY a.created_at ASC",
        'i', [$grievanceId]
    ));

    $canManage = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_grievances', 'manage'));

    if (!$canManage) {
        $actions = array_values(array_filter($actions, function ($a) {
            return empty($a['is_internal']);
        }));
    }

    jsonResponse(true, 'OK', $actions);
} catch (Throwable $e) {
    error_log('get-grievance-actions.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
