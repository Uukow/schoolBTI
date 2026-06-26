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
    "SELECT e.*, s.section_name, u.username as instructor_name FROM lab_experiments e
     LEFT JOIN lab_sections s ON e.section_id = s.id
     LEFT JOIN users u ON e.instructor_id = u.id
     WHERE e.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Experiment not found');
jsonResponse(true, '', $row);
