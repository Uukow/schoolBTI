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

    $programId = (int)($_GET['program_id'] ?? 0);
    if (!$programId) {
        jsonResponse(false, 'Program ID required');
    }

    $participants = fetchAll(executeQuery(
        "SELECT pt.*, s.staff_id, s.first_name, s.last_name, s.designation, s.department, s.email
         FROM hr_ppdp_participants pt
         INNER JOIN staff s ON pt.staff_id = s.id
         WHERE pt.program_id = ?
         ORDER BY pt.registration_date DESC, s.first_name",
        'i', [$programId]
    ));

    jsonResponse(true, 'OK', $participants);
} catch (Throwable $e) {
    error_log('get-ppdp-participants.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
