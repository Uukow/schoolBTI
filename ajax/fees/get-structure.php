<?php
/**
 * AJAX: Get Fee Structure Details
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

$sql = "SELECT * FROM fee_structures WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$structureId]);
$structure = fetchOne($stmt);

if (!$structure) {
    jsonResponse(false, 'Fee structure not found');
}

jsonResponse(true, 'Fee structure loaded', $structure);

