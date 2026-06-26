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
$staffId = (int)($_POST['staff_id'] ?? 0);
if (!$staffId) {
    jsonResponse(false, 'Staff is required');
}

$types = $_POST['doc_type'] ?? [];
$names = $_POST['doc_name'] ?? [];
$expiries = $_POST['doc_expiry'] ?? [];
$files = $_FILES['doc_file'] ?? null;

if (!$files || !is_array($files['name'])) {
    jsonResponse(false, 'No documents to upload');
}

if (!is_dir(STAFF_DOCS_PATH)) {
    @mkdir(STAFF_DOCS_PATH, 0755, true);
}

$uploaded = 0;
$errors = [];

for ($i = 0, $count = count($types); $i < $count; $i++) {
    if (empty($files['name'][$i]) || ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        continue;
    }
    if (($files['error'][$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = ($names[$i] ?? 'Document') . ': upload failed';
        continue;
    }

    $documentType = sanitize($types[$i] ?? 'Other');
    $documentName = sanitize($names[$i] ?? $documentType);
    $expiryDate = !empty($expiries[$i]) ? sanitize($expiries[$i]) : null;

    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $errors[] = $documentName . ': file type not allowed';
        continue;
    }

    $filename = 'doc_' . $staffId . '_' . preg_replace('/\W+/', '_', $documentType) . '_' . time() . '_' . $i . '.' . $ext;
    $dest = STAFF_DOCS_PATH . $filename;

    if (!move_uploaded_file($files['tmp_name'][$i], $dest)) {
        $errors[] = $documentName . ': failed to save file';
        continue;
    }

    $relativePath = 'uploads/staff/documents/' . $filename;
    $stmt = executeQuery(
        "INSERT INTO hr_employee_documents (staff_id, document_type, document_name, file_path, expiry_date, uploaded_by)
         VALUES (?, ?, ?, ?, ?, ?)",
        'issssi',
        [$staffId, $documentType, $documentName, $relativePath, $expiryDate, $currentUser['id']]
    );

    if ($stmt) {
        $uploaded++;
    } else {
        $errors[] = $documentName . ': database error';
    }
}

if ($uploaded > 0) {
    logActivity($currentUser['id'], 'Upload Employee Documents', 'HR', "Staff ID: $staffId ($uploaded files)");
}

if ($uploaded === 0) {
    jsonResponse(false, $errors ? implode('; ', $errors) : 'Select at least one file to upload');
}

$message = $uploaded . ' document(s) uploaded successfully';
if ($errors) {
    $message .= '. Some failed: ' . implode('; ', $errors);
}
jsonResponse(true, $message, ['uploaded' => $uploaded, 'errors' => $errors]);
