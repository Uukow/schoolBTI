<?php
/**
 * API Notifications Endpoint
 * 
 * Retrieves user notifications with filtering and pagination
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get user from request
$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    sendApiResponse(false, 'User ID is required', null, 400);
}

// Fetch user
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?";
$stmt = executeQuery($sql, 'i', [$userId]);
$user = fetchOne($stmt);

if (!$user) {
    sendApiResponse(false, 'User not found', null, 404);
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;
$offset = ($page - 1) * $limit;

// Get filter parameters
$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

// Build query
$whereClause = "n.user_id = ?";
$params = [$userId];
$types = 'i';

if ($unreadOnly) {
    $whereClause .= " AND n.is_read = 0";
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM notifications n WHERE $whereClause";
$countStmt = executeQuery($countSql, $types, $params);
$totalCount = fetchOne($countStmt)['total'] ?? 0;

// Get notifications
$sql = "SELECT n.*, 
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 1 THEN 'Just now'
            WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' min ago')
            WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' hours ago')
            WHEN TIMESTAMPDIFF(DAY, n.created_at, NOW()) < 7 THEN CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' days ago')
            ELSE DATE_FORMAT(n.created_at, '%b %d, %Y')
        END as time_ago
        FROM notifications n
        WHERE $whereClause
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = executeQuery($sql, $types, $params);
$notifications = fetchAll($stmt);

// Get unread count
$unreadCountSql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$unreadStmt = executeQuery($unreadCountSql, 'i', [$userId]);
$unreadCount = fetchOne($unreadStmt)['count'] ?? 0;

// Prepare response
$responseData = [
    'notifications' => $notifications,
    'unread_count' => $unreadCount,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total' => $totalCount,
        'total_pages' => ceil($totalCount / $limit),
        'has_more' => ($page * $limit) < $totalCount
    ]
];

sendApiResponse(true, 'Notifications retrieved successfully', $responseData);














