<?php
/**
 * AJAX: Update Fee Structure
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$structureId = (int)($_POST['id'] ?? 0);
$classId = (int)($_POST['class_id'] ?? 0);
$feeTypeId = (int)($_POST['fee_type_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$frequency = $_POST['frequency'] ?? 'Monthly';
$dueDate = $_POST['due_date'] ?? null;
$isMandatory = isset($_POST['is_mandatory']) ? 1 : 0;

if (empty($structureId) || empty($classId) || empty($feeTypeId) || $amount <= 0) {
    jsonResponse(false, 'All required fields must be filled');
}

// Check if structure exists
$checkSql = "SELECT id, session_id FROM fee_structures WHERE id = ?";
$checkStmt = executeQuery($checkSql, 'i', [$structureId]);
$existingStructure = fetchOne($checkStmt);

if (!$existingStructure) {
    jsonResponse(false, 'Fee structure not found');
}

// Check if another structure with same class, fee type, and session exists (excluding current)
$duplicateSql = "SELECT id FROM fee_structures WHERE class_id = ? AND fee_type_id = ? AND session_id = ? AND id != ?";
$duplicateStmt = executeQuery($duplicateSql, 'iiii', [$classId, $feeTypeId, $existingStructure['session_id'], $structureId]);
if (fetchOne($duplicateStmt)) {
    jsonResponse(false, 'Fee structure already exists for this class and fee type');
}

$sql = "UPDATE fee_structures SET class_id = ?, fee_type_id = ?, amount = ?, frequency = ?, due_date = ?, is_mandatory = ? WHERE id = ?";

$stmt = executeQuery($sql, 'iidssii', [
    $classId, $feeTypeId, $amount, $frequency, $dueDate, $isMandatory, $structureId
]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Update Fee Structure', 'Fees', "Updated fee structure ID: $structureId");
    jsonResponse(true, 'Fee structure updated successfully!');
} else {
    jsonResponse(false, 'Failed to update fee structure');
}

