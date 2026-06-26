<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$token = bin2hex(random_bytes(16));
$branchId = !empty($data['branch_id']) ? (int)$data['branch_id'] : ($currentUser['branch_id'] ?? null);
$location = sanitize($data['location_name'] ?? 'Main Gate');
$validDate = sanitize($data['valid_date'] ?? date('Y-m-d'));
$validFrom = !empty($data['valid_from']) ? sanitize($data['valid_from']) : null;
$validUntil = !empty($data['valid_until']) ? sanitize($data['valid_until']) : null;

executeQuery(
    "INSERT INTO hr_qr_sessions (session_token, branch_id, location_name, valid_date, valid_from, valid_until, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?)",
    'sissssi',
    [$token, $branchId, $location, $validDate, $validFrom, $validUntil, $currentUser['id']]
);

$qrUrl = APP_URL . 'modules/hr/qr-scan.php?token=' . $token;
jsonResponse(true, 'QR session created', [
    'token' => $token,
    'qr_url' => $qrUrl,
    'qr_image' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrUrl),
]);
