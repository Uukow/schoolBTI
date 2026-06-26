<?php
/**
 * AJAX: Reload Settings
 * 
 * Reload settings from database and clear cache
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

// Initialize Settings Manager
$settingsManager = SettingsManager::getInstance();

// Reload settings
$settingsManager->reload();

// Log activity
$currentUser = getCurrentUser();
logActivity($currentUser['id'] ?? null, 'Reload Settings', 'Settings', 'Reloaded system settings from database');

jsonResponse(true, 'Settings reloaded successfully!');

