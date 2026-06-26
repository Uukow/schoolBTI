<?php
/**
 * Check which settings columns exist in database
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Unauthorized');
}

// Get all columns from system_settings table
$sql = "SHOW COLUMNS FROM system_settings";
$stmt = executeQuery($sql);
$columns = fetchAll($stmt);

$columnNames = array_column($columns, 'Field');

jsonResponse(true, 'Columns retrieved', ['columns' => $columnNames]);

