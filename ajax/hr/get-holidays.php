<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

$currentUser = getCurrentUser();
$year = (int)($_GET['year'] ?? date('Y'));
$branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : null;
$holidayType = sanitize($_GET['holiday_type'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$yearStart = $year . '-01-01';
$yearEnd = $year . '-12-31';

$sql = "SELECT h.*, b.branch_name,
        COALESCE(h.end_date, h.holiday_date) as end_date_resolved,
        DATEDIFF(COALESCE(h.end_date, h.holiday_date), h.holiday_date) + 1 as duration_days
        FROM hr_holidays h
        LEFT JOIN branches b ON h.branch_id = b.id
        WHERE h.holiday_date <= ? AND COALESCE(h.end_date, h.holiday_date) >= ?";
$params = [$yearEnd, $yearStart];
$types = 'ss';

if ($branchId) {
    $sql .= " AND (h.branch_id IS NULL OR h.branch_id = ?)";
    $params[] = $branchId;
    $types .= 'i';
} elseif (!hasRole(['Super Admin'])) {
    $sql .= " AND (h.branch_id IS NULL OR h.branch_id = ?)";
    $params[] = $currentUser['branch_id'];
    $types .= 'i';
}

if ($holidayType) {
    $sql .= " AND h.holiday_type = ?";
    $params[] = $holidayType;
    $types .= 's';
}

if ($search) {
    $sql .= " AND (h.holiday_name LIKE ? OR h.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql .= " ORDER BY h.holiday_date ASC";
$rows = fetchAll(executeQuery($sql, $types, $params));

$today = date('Y-m-d');
$stats = ['total' => count($rows), 'total_days' => 0, 'upcoming' => 0, 'public' => 0];
foreach ($rows as $row) {
    $stats['total_days'] += (int)($row['duration_days'] ?? 1);
    if ($row['holiday_type'] === 'Public') {
        $stats['public']++;
    }
    $end = $row['end_date_resolved'];
    if ($end >= $today) {
        $stats['upcoming']++;
    }
}

jsonResponse(true, 'Holidays loaded', ['holidays' => $rows, 'stats' => $stats]);
