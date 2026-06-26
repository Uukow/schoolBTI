<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$currentUser = getCurrentUser();
$staffId = (int)($_POST['staff_id'] ?? 0);
$documentType = sanitize($_POST['document_type'] ?? '');
$documentName = sanitize($_POST['document_name'] ?? '');
$expiryDate = !empty($_POST['expiry_date']) ? sanitize($_POST['expiry_date']) : null;
$notes = sanitize($_POST['notes'] ?? '');

if (!$staffId || empty($documentType) || empty($documentName)) {
    jsonResponse(false, 'Staff, document type and name are required');
}

if (empty($_FILES['document_file']['name'])) {
    jsonResponse(false, 'Please upload a document file');
}

if (!is_dir(STAFF_DOCS_PATH)) {
    @mkdir(STAFF_DOCS_PATH, 0755, true);
}

$ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ALLOWED_EXTENSIONS)) {
    jsonResponse(false, 'File type not allowed');
}

$filename = 'doc_' . $staffId . '_' . time() . '.' . $ext;
$dest = STAFF_DOCS_PATH . $filename;

if (!move_uploaded_file($_FILES['document_file']['tmp_name'], $dest)) {
    jsonResponse(false, 'Failed to upload file');
}

$relativePath = 'uploads/staff/documents/' . $filename;
$sql = "INSERT INTO hr_employee_documents (staff_id, document_type, document_name, file_path, expiry_date, notes, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = executeQuery($sql, 'isssssi', [$staffId, $documentType, $documentName, $relativePath, $expiryDate, $notes, $currentUser['id']]);

if ($stmt) {
    logActivity($currentUser['id'], 'Upload Employee Document', 'HR', "Staff ID: $staffId");
    jsonResponse(true, 'Document uploaded successfully');
}
jsonResponse(false, 'Failed to save document');
