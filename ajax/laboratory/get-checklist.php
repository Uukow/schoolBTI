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
    "SELECT c.*, s.section_name, u.username AS inspector_name
     FROM lab_safety_checklists c
     LEFT JOIN lab_sections s ON c.section_id = s.id
     LEFT JOIN users u ON c.inspector_id = u.id
     WHERE c.id = ?",
    'i',
    [$id]
));

if (!$row) jsonResponse(false, 'Checklist not found');

$items = json_decode($row['items_checked'] ?? '[]', true);
if (!is_array($items)) {
    $items = [];
}
$row['items'] = $items;

jsonResponse(true, '', $row);
