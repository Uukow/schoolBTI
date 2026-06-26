<?php
ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL); ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');

$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonResponse(false, 'Invalid ID');

$row = fetchOne(executeQuery(
    "SELECT m.*, i.item_title, s.section_name, t.username as technician_name
     FROM lab_maintenance_records m
     LEFT JOIN lab_inventory_items i ON m.item_id = i.id
     LEFT JOIN lab_sections s ON m.section_id = s.id
     LEFT JOIN users t ON m.assigned_technician = t.id
     WHERE m.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Record not found');
jsonResponse(true, '', $row);
