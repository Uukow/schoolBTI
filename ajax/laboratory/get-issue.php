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
    "SELECT i.*, t.type_name, s.section_name, r.username as reporter_name, a.username as assignee_name
     FROM lab_issues i
     LEFT JOIN lab_issue_types t ON i.issue_type_id = t.id
     LEFT JOIN lab_sections s ON i.section_id = s.id
     LEFT JOIN users r ON i.reported_by = r.id
     LEFT JOIN users a ON i.assigned_to = a.id
     WHERE i.id = ?", 'i', [$id]
));

if (!$row) jsonResponse(false, 'Issue not found');
jsonResponse(true, '', $row);
