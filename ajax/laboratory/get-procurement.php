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
    "SELECT p.*, c.category_name, s.section_name FROM lab_procurement p
     LEFT JOIN lab_inventory_categories c ON p.category_id = c.id
     LEFT JOIN lab_sections s ON p.section_id = s.id
     WHERE p.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Record not found');
jsonResponse(true, '', $row);
