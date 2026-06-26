<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Lab Director'])) jsonResponse(false, 'Permission denied');

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$currentUser = getCurrentUser();

// Unlink inventory items from this section
executeQuery("UPDATE lab_inventory_items SET section_id=NULL WHERE section_id=?", 'i', [$id]);

$stmt = executeQuery("DELETE FROM lab_sections WHERE id=?", 'i', [$id]);
if ($stmt) {
    logActivity($currentUser['id'], 'Delete Lab Section', 'Laboratory', "Deleted section ID: $id");
    jsonResponse(true, 'Laboratory section deleted');
} else {
    jsonResponse(false, 'Failed to delete section');
}
