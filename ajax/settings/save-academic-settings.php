<?php
/**
 * AJAX: Save Academic Settings
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$academicYearStartMonth = (int)($_POST['academic_year_start_month'] ?? 1);
$termsPerYear = (int)($_POST['terms_per_year'] ?? 2);

// Check if columns exist, if not add them
try {
    $checkColSql = "SHOW COLUMNS FROM system_settings LIKE 'academic_year_start_month'";
    $colCheck = executeQuery($checkColSql);
    if (!fetchOne($colCheck)) {
        // Add columns if they don't exist
        $alterSql = "ALTER TABLE system_settings 
                     ADD COLUMN academic_year_start_month INT DEFAULT 1,
                     ADD COLUMN terms_per_year INT DEFAULT 2";
        executeQuery($alterSql);
    }
} catch (Exception $e) {
    // Columns might already exist, continue
}

// Get current settings
$settingsSql = "SELECT * FROM system_settings LIMIT 1";
$settings = fetchOne(executeQuery($settingsSql));

if ($settings) {
    $sql = "UPDATE system_settings SET academic_year_start_month = ?, terms_per_year = ? WHERE id = ?";
    $stmt = executeQuery($sql, 'iii', [$academicYearStartMonth, $termsPerYear, $settings['id']]);
} else {
    $sql = "INSERT INTO system_settings (school_name, academic_year_start_month, terms_per_year) VALUES (?, ?, ?)";
    $stmt = executeQuery($sql, 'sii', ['School', $academicYearStartMonth, $termsPerYear]);
}

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Save Academic Settings', 'Settings', 'Updated academic settings');
    jsonResponse(true, 'Academic settings saved successfully!');
} else {
    jsonResponse(false, 'Failed to save settings');
}

