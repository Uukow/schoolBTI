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
$data = $_POST;
if (empty($data) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
}

$id = (int)($data['id'] ?? 0);
$staffId = (int)($data['staff_id'] ?? 0);
$contractType = sanitize($data['contract_type'] ?? 'Permanent');
$startDate = sanitize($data['start_date'] ?? '');
$endDate = !empty($data['end_date']) ? sanitize($data['end_date']) : null;
$salaryAmount = isset($data['salary_amount']) && $data['salary_amount'] !== '' ? (float)$data['salary_amount'] : null;
$notes = sanitize($data['notes'] ?? '');
$status = sanitize($data['status'] ?? 'Active');

if (!$staffId || empty($startDate)) {
    jsonResponse(false, 'Staff and start date are required');
}

if ($contractType !== 'Permanent' && empty($endDate)) {
    jsonResponse(false, 'End date is required for non-permanent contracts');
}

$filePath = null;
if (!empty($_FILES['contract_file']['name'])) {
    if (!is_dir(STAFF_CONTRACTS_PATH)) {
        @mkdir(STAFF_CONTRACTS_PATH, 0755, true);
    }
    $ext = strtolower(pathinfo($_FILES['contract_file']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        jsonResponse(false, 'File type not allowed');
    }
    $filename = 'contract_' . $staffId . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($_FILES['contract_file']['tmp_name'], STAFF_CONTRACTS_PATH . $filename)) {
        jsonResponse(false, 'Failed to upload contract file');
    }
    $filePath = 'uploads/staff/contracts/' . $filename;
}

if ($id > 0) {
    if ($filePath) {
        $stmt = executeQuery(
            "UPDATE hr_contracts SET staff_id=?, contract_type=?, start_date=?, end_date=?, salary_amount=?, status=?, notes=?, file_path=? WHERE id=?",
            'isssdsssi',
            [$staffId, $contractType, $startDate, $endDate, $salaryAmount, $status, $notes, $filePath, $id]
        );
    } else {
        $stmt = executeQuery(
            "UPDATE hr_contracts SET staff_id=?, contract_type=?, start_date=?, end_date=?, salary_amount=?, status=?, notes=? WHERE id=?",
            'isssdssi',
            [$staffId, $contractType, $startDate, $endDate, $salaryAmount, $status, $notes, $id]
        );
    }
    if ($stmt) {
        logActivity($currentUser['id'], 'Update Contract', 'HR', "Contract ID: $id");
        jsonResponse(true, 'Contract updated successfully');
    }
    jsonResponse(false, 'Failed to update contract');
}

$contractNo = HrNumberService::next('CTR-', 'hr_contracts', 'contract_no');
$stmt = executeQuery(
    "INSERT INTO hr_contracts (contract_no, staff_id, contract_type, start_date, end_date, salary_amount, status, file_path, notes, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
    'sisssdsssi',
    [$contractNo, $staffId, $contractType, $startDate, $endDate, $salaryAmount, $status, $filePath, $notes, $currentUser['id']]
);

if ($stmt) {
    logActivity($currentUser['id'], 'Create Contract', 'HR', "Contract: $contractNo");
    jsonResponse(true, 'Contract created successfully', ['contract_no' => $contractNo]);
}
jsonResponse(false, 'Failed to save contract');
