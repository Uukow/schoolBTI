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

    $currentUser = getCurrentUser();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = sanitize($data['action'] ?? 'update');

    $canManage = hasRole(['Super Admin', 'Admin'])
        || (function_exists('canPerform') && canPerform('hr_ppdp', 'manage'));

    $participantId = (int)($data['participant_id'] ?? 0);
    $programId = (int)($data['program_id'] ?? 0);
    $staffId = (int)($data['staff_id'] ?? 0);

    if ($action === 'add' && $canManage && $programId && $staffId) {
        $program = fetchOne(executeQuery("SELECT * FROM hr_ppdp_programs WHERE id = ?", 'i', [$programId]));
        if (!$program) {
            jsonResponse(false, 'Program not found');
        }
        $count = (int)(fetchOne(executeQuery(
            "SELECT COUNT(*) AS c FROM hr_ppdp_participants WHERE program_id = ?",
            'i', [$programId]
        ))['c'] ?? 0);
        if ($count >= (int)$program['capacity']) {
            jsonResponse(false, 'Program is at full capacity');
        }
        $exists = fetchOne(executeQuery(
            "SELECT id FROM hr_ppdp_participants WHERE program_id = ? AND staff_id = ?",
            'ii', [$programId, $staffId]
        ));
        if ($exists) {
            jsonResponse(false, 'Staff member is already enrolled');
        }
        executeQuery(
            "INSERT INTO hr_ppdp_participants (program_id, staff_id, registration_date, status)
             VALUES (?, ?, CURDATE(), 'Registered')",
            'ii', [$programId, $staffId]
        );
        logActivity($currentUser['id'], 'Add PPDP Participant', 'HR', "Program $programId, staff $staffId");
        jsonResponse(true, 'Participant added');
    }

    if ($action === 'remove' && $canManage && $participantId) {
        executeQuery("DELETE FROM hr_ppdp_participants WHERE id = ?", 'i', [$participantId]);
        logActivity($currentUser['id'], 'Remove PPDP Participant', 'HR', "Participant ID $participantId");
        jsonResponse(true, 'Participant removed');
    }

    if ($participantId && $canManage) {
        $allowed = ['Registered', 'Attending', 'Completed', 'Dropped', 'Failed'];
        $status = sanitize($data['status'] ?? '');
        if ($status && !in_array($status, $allowed, true)) {
            jsonResponse(false, 'Invalid participant status');
        }
        $progress = isset($data['progress_percent']) ? max(0, min(100, (int)$data['progress_percent'])) : null;
        $score = isset($data['assessment_score']) && $data['assessment_score'] !== ''
            ? (float)$data['assessment_score'] : null;
        $certificateNo = isset($data['certificate_no']) ? sanitize($data['certificate_no']) : null;

        if ($status) {
            executeQuery(
                "UPDATE hr_ppdp_participants SET status = ?, progress_percent = COALESCE(?, progress_percent),
                 assessment_score = COALESCE(?, assessment_score), certificate_no = COALESCE(?, certificate_no)
                 WHERE id = ?",
                'sddsi',
                [$status, $progress, $score, $certificateNo, $participantId]
            );
        } elseif ($progress !== null || $score !== null || $certificateNo !== null) {
            executeQuery(
                "UPDATE hr_ppdp_participants SET progress_percent = COALESCE(?, progress_percent),
                 assessment_score = COALESCE(?, assessment_score), certificate_no = COALESCE(?, certificate_no)
                 WHERE id = ?",
                'ddsi',
                [$progress, $score, $certificateNo, $participantId]
            );
        }
        if ($status === 'Completed' && empty($certificateNo)) {
            $cert = HrNumberService::next('CERT-', 'hr_ppdp_participants', 'certificate_no');
            executeQuery("UPDATE hr_ppdp_participants SET certificate_no = ?, progress_percent = 100 WHERE id = ?", 'si', [$cert, $participantId]);
        }
        jsonResponse(true, 'Participant updated');
    }

    jsonResponse(false, 'Invalid request');
} catch (Throwable $e) {
    error_log('save-ppdp-participant.php: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
