<?php
/**
 * AJAX: Get Permission Audit Log
 * 
 * Retrieves audit log entries for permission changes
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasRole(['Super Admin'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Get filters
$filters = [];
if (!empty($_GET['target_type'])) {
    $filters['target_type'] = sanitize($_GET['target_type']);
}
if (!empty($_GET['target_id'])) {
    $filters['target_id'] = (int)$_GET['target_id'];
}
if (!empty($_GET['user_id'])) {
    $filters['user_id'] = (int)$_GET['user_id'];
}
if (!empty($_GET['module_id'])) {
    $filters['module_id'] = (int)$_GET['module_id'];
}
if (!empty($_GET['action_id'])) {
    $filters['action_id'] = (int)$_GET['action_id'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = sanitize($_GET['date_from']);
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = sanitize($_GET['date_to']);
}

$limit = (int)($_GET['limit'] ?? 100);
$offset = (int)($_GET['offset'] ?? 0);

// Get audit log
$auditLog = PermissionManager::getAuditLog($filters, $limit, $offset);

// Format dates
foreach ($auditLog as &$entry) {
    $entry['created_at'] = date('d-m-Y H:i:s', strtotime($entry['created_at']));
}

echo json_encode([
    'success' => true,
    'data' => $auditLog,
    'count' => count($auditLog)
]);

