<?php
/**
 * AJAX: Save Settings
 * 
 * Save system settings with validation and audit logging
 * 
 * @author School ERP Development Team
 * @version 2.0.0
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied');
}

// Get JSON input
$rawInput = file_get_contents('php://input');

// Debug logging
error_log("Settings Save - Raw Input: " . $rawInput);

// Try to decode JSON
$input = json_decode($rawInput, true);

// If JSON decode failed, try to get from POST (fallback)
if (!$input && !empty($_POST)) {
    $input = $_POST;
    error_log("Settings Save - Using POST data instead");
}

// Debug logging
error_log("Settings Save - Parsed Input: " . print_r($input, true));

if (!$input || !is_array($input) || empty($input)) {
    $errorMsg = 'Invalid input data. ';
    if (!empty($rawInput)) {
        $errorMsg .= 'Received: ' . substr($rawInput, 0, 200);
    } else {
        $errorMsg .= 'No data received.';
    }
    error_log("Settings Save - Error: " . $errorMsg);
    jsonResponse(false, $errorMsg);
}

// Initialize Settings Manager
$settingsManager = SettingsManager::getInstance();
$currentUser = getCurrentUser();
$userId = $currentUser['id'] ?? null;

// Get all existing columns first
$columnsSql = "SHOW COLUMNS FROM system_settings";
$columnsStmt = executeQuery($columnsSql);
$allColumns = fetchAll($columnsStmt);
$existingColumns = array_column($allColumns, 'Field');

// Get current settings record first
$sql = "SELECT id FROM system_settings LIMIT 1";
$stmt = executeQuery($sql);
$settingsRecord = fetchOne($stmt);

if (!$settingsRecord) {
    // Create initial settings record
    $createSql = "INSERT INTO system_settings (school_name, created_at) VALUES (?, NOW())";
    $createStmt = executeQuery($createSql, 's', ['School']);
    if ($createStmt) {
        $conn = getDBConnection();
        $settingsRecord = ['id' => mysqli_insert_id($conn)];
    } else {
        jsonResponse(false, 'Failed to initialize settings record');
    }
}

// Save settings - use direct database update for better reliability
$results = [];
$errors = [];
$warnings = [];
$savedCount = 0;
$skippedCount = 0;

foreach ($input as $key => $value) {
    // Skip empty password fields (they should remain unchanged)
    if (in_array($key, ['smtp_password', 'sms_api_key', 'whatsapp_api_key', 'payment_api_key', 'api_key', 'license_key']) && empty($value)) {
        $skippedCount++;
        continue;
    }
    
    // Skip file inputs (need separate handling)
    if (empty($key) || $key === 'system_logo' || $key === 'system_favicon') {
        $skippedCount++;
        continue;
    }
    
    // Check if column exists
    if (!in_array($key, $existingColumns)) {
        $warnings[] = "Column '{$key}' does not exist in database. Skipping.";
        $results[$key] = false;
        continue;
    }
    
    // Convert checkbox values
    if (is_string($value) && ($value === 'true' || $value === 'false')) {
        $value = $value === 'true' ? 1 : 0;
    }
    
    // Convert numeric strings to proper types
    if (is_numeric($value) && strpos($value, '.') !== false) {
        $value = (float)$value;
    } elseif (is_numeric($value)) {
        $value = (int)$value;
    }
    
    // Handle NULL values
    if ($value === '' || $value === null) {
        $value = null;
    }
    
    try {
        // Encrypt sensitive fields
        $sensitiveFields = [
            'smtp_password', 'sms_api_key', 'whatsapp_api_key', 
            'payment_api_key', 'payment_api_secret', 'api_key', 'license_key'
        ];
        
        $dbValue = $value;
        if (in_array($key, $sensitiveFields) && !empty($value)) {
            // For now, save as plain text (encryption can be added later)
            $dbValue = $value;
        }
        
        // Direct database update
        $updateSql = "UPDATE system_settings SET `{$key}` = ?, `updated_at` = NOW(), `updated_by` = ? WHERE id = ?";
        
        // Determine parameter type
        $paramType = 's'; // default to string
        if (is_int($value)) {
            $paramType = 'i';
        } elseif (is_float($value)) {
            $paramType = 'd';
        } elseif ($value === null) {
            $paramType = 's'; // NULL as string
            $dbValue = null;
        }
        
        $updateStmt = executeQuery($updateSql, $paramType . 'ii', [$dbValue, $userId, $settingsRecord['id']]);
        
        if ($updateStmt) {
            $results[$key] = true;
            $savedCount++;
            error_log("Settings Save - Successfully saved: {$key} = " . (is_string($value) ? substr($value, 0, 50) : $value));
        } else {
            $results[$key] = false;
            $errors[] = "Failed to save '{$key}'";
            error_log("Settings Save - Failed to save: {$key}");
        }
    } catch (Exception $e) {
        $results[$key] = false;
        $errors[] = "Error saving '{$key}': " . $e->getMessage();
        error_log("Settings Save - Exception for {$key}: " . $e->getMessage());
    }
}

// Check if any settings were saved
if ($savedCount > 0) {
    // Log activity
    logActivity($userId, 'Update Settings', 'Settings', "Updated {$savedCount} system settings");
    
    $message = "Settings saved successfully! ({$savedCount} settings saved)";
    if (count($warnings) > 0) {
        $message .= ". " . count($warnings) . " settings skipped (columns don't exist).";
    }
    if (count($errors) > 0) {
        $message .= ". " . count($errors) . " errors occurred.";
    }
    
    jsonResponse(true, $message, [
        'saved' => $savedCount,
        'skipped' => $skippedCount + count($warnings),
        'errors' => $errors,
        'warnings' => $warnings,
        'results' => $results,
        'existing_columns' => $existingColumns
    ]);
} else {
    $message = 'No settings were saved. ';
    if (count($warnings) > 0) {
        $message .= implode(' ', array_slice($warnings, 0, 3)) . ' ';
    }
    if (count($errors) > 0) {
        $message .= implode(' ', array_slice($errors, 0, 3));
    }
    if (empty($warnings) && empty($errors)) {
        $message .= 'No valid settings to save.';
    }
    
    jsonResponse(false, $message, [
        'results' => $results, 
        'errors' => $errors,
        'warnings' => $warnings,
        'existing_columns' => $existingColumns
    ]);
}

