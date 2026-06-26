<?php
/**
 * AJAX: Get Fee Structure Details (with related data)
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Accountant'])) jsonResponse(false, 'Permission denied');

$structureId = $_GET['id'] ?? 0;

if (empty($structureId)) {
    jsonResponse(false, 'Invalid structure ID');
}

$sql = "SELECT fs.*, c.class_name, c.class_code, ft.fee_name, ft.fee_code, s.session_name
        FROM fee_structures fs
        LEFT JOIN classes c ON fs.class_id = c.id
        LEFT JOIN fee_types ft ON fs.fee_type_id = ft.id
        LEFT JOIN academic_sessions s ON fs.session_id = s.id
        WHERE fs.id = ?";
$stmt = executeQuery($sql, 'i', [$structureId]);
$structure = fetchOne($stmt);

if (!$structure) {
    jsonResponse(false, 'Fee structure not found');
}

jsonResponse(true, 'Fee structure loaded', $structure);

