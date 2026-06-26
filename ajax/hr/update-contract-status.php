<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

$currentUser = getCurrentUser();
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$id = (int)($data['contract_id'] ?? 0);
$action = sanitize($data['action'] ?? '');

if (!$id || !$action) {
    jsonResponse(false, 'Contract and action required');
}

$contract = fetchOne(executeQuery(
    "SELECT * FROM hr_contracts WHERE id = ?", 'i', [$id]
));
if (!$contract) {
    jsonResponse(false, 'Contract not found');
}

switch ($action) {
    case 'terminate':
        executeQuery("UPDATE hr_contracts SET status = 'Terminated' WHERE id = ?", 'i', [$id]);
        logActivity($currentUser['id'], 'Terminate Contract', 'HR', "Contract ID: $id");
        jsonResponse(true, 'Contract terminated');

    case 'activate':
        executeQuery("UPDATE hr_contracts SET status = 'Active' WHERE id = ?", 'i', [$id]);
        jsonResponse(true, 'Contract activated');

    case 'renew':
        $newStart = sanitize($data['start_date'] ?? date('Y-m-d'));
        $newEnd = !empty($data['end_date']) ? sanitize($data['end_date']) : null;
        $newSalary = isset($data['salary_amount']) && $data['salary_amount'] !== ''
            ? (float)$data['salary_amount'] : $contract['salary_amount'];

        executeQuery("UPDATE hr_contracts SET status = 'Renewed' WHERE id = ?", 'i', [$id]);

        $contractNo = HrNumberService::next('CTR-', 'hr_contracts', 'contract_no');
        executeQuery(
            "INSERT INTO hr_contracts (contract_no, staff_id, contract_type, start_date, end_date, salary_amount, status, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, 'Active', ?, ?)",
            'sisssdsi',
            [
                $contractNo,
                $contract['staff_id'],
                $contract['contract_type'],
                $newStart,
                $newEnd,
                $newSalary,
                'Renewed from ' . $contract['contract_no'],
                $currentUser['id'],
            ]
        );
        logActivity($currentUser['id'], 'Renew Contract', 'HR', "From {$contract['contract_no']} to $contractNo");
        jsonResponse(true, 'Contract renewed', ['contract_no' => $contractNo]);

    default:
        jsonResponse(false, 'Unknown action');
}
