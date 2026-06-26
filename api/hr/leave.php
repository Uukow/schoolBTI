<?php
ob_start();
require_once '../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();

require_once __DIR__ . '/_auth.php';

try {
    $auth = hrApiAuth();
    $staffId = (int)($_GET['staff_id'] ?? $_POST['staff_id'] ?? $auth['staff_id'] ?? 0);
    $bf = hrApiBranchFilter($auth, 's');

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $status = sanitize($_GET['status'] ?? '');
        $sql = "SELECT la.*, lt.leave_name, s.staff_id as staff_code, s.first_name, s.last_name
                FROM leave_applications la
                INNER JOIN staff s ON la.staff_id = s.id
                INNER JOIN leave_types lt ON la.leave_type_id = lt.id
                WHERE 1=1" . $bf['sql'];
        $params = $bf['params'];
        $types = $bf['types'];
        if ($staffId) {
            $sql .= " AND la.staff_id = ?";
            $params[] = $staffId;
            $types .= 'i';
        }
        if ($status) {
            $sql .= " AND la.approval_stage = ?";
            $params[] = $status;
            $types .= 's';
        }
        $sql .= " ORDER BY la.created_at DESC";
        sendApiResponse(true, 'Leave applications', fetchAll(executeQuery($sql, $types, $params)));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (!$staffId) {
            sendApiResponse(false, 'Staff ID required', null, 400);
        }
        executeQuery(
            "INSERT INTO leave_applications (staff_id, leave_type_id, start_date, end_date, total_days, reason, approval_stage)
             VALUES (?, ?, ?, ?, ?, ?, 'Pending')",
            'iissis',
            [$staffId, (int)$data['leave_type_id'], sanitize($data['start_date']), sanitize($data['end_date']),
             (int)$data['total_days'], sanitize($data['reason'] ?? '')]
        );
        sendApiResponse(true, 'Leave application submitted');
    }

    sendApiResponse(false, 'Invalid request method', null, 405);
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}
