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
    "SELECT i.*, s.section_name, u.username as reporter_name FROM lab_safety_incidents i
     LEFT JOIN lab_sections s ON i.section_id = s.id
     LEFT JOIN users u ON i.reported_by = u.id
     WHERE i.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Incident not found');
jsonResponse(true, '', $row);
