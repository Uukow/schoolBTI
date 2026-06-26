<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$currentUser   = getCurrentUser();
$visitorName   = sanitize($_POST['visitor_name']      ?? '');
$visitorId     = sanitize($_POST['visitor_id_number'] ?? '');
$organization  = sanitize($_POST['organization']      ?? '');
$contact       = sanitize($_POST['contact_number']    ?? '');
$purpose       = sanitize($_POST['purpose']           ?? '');
$hostId        = !empty($_POST['host_id']) ? (int)$_POST['host_id'] : null;
$hostName      = sanitize($_POST['host_name']  ?? '');
$sectionId     = !empty($_POST['section_id'])  ? (int)$_POST['section_id']  : null;
$entryTime     = !empty($_POST['entry_time'])  ? sanitize($_POST['entry_time']) : null;
$notes         = sanitize($_POST['notes'] ?? '');
$checkInNow    = !empty($_POST['check_in_now']);

if (empty($visitorName)) jsonResponse(false, 'Visitor name is required');
if (empty($purpose))     jsonResponse(false, 'Purpose of visit is required');

// Generate visitor pass
$pass   = 'VP-' . strtoupper(substr($visitorName, 0, 3)) . '-' . date('YmdHi');
$status = $checkInNow ? 'checked_in' : 'expected';
$entryTime = $checkInNow ? date('Y-m-d H:i:s') : $entryTime;

$sql = "INSERT INTO lab_visitors (visitor_name, visitor_id_number, organization, contact_number, purpose,
        host_id, host_name, section_id, visitor_pass, entry_time, status, notes, created_by, branch_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'sssssissssssii', [
    $visitorName, $visitorId, $organization, $contact, $purpose,
    $hostId, $hostName, $sectionId, $pass, $entryTime, $status,
    $notes, $currentUser['id'], $currentUser['branch_id']
]);

if ($stmt) {
    logActivity($currentUser['id'], 'Register Visitor', 'Laboratory', "Visitor registered: $visitorName (Pass: $pass)");
    jsonResponse(true, "Visitor registered! Pass: $pass");
} else {
    jsonResponse(false, 'Failed to register visitor');
}
