<?php
/**
 * AJAX: Delete Quiz
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin', 'Teacher'])) jsonResponse(false, 'Permission denied');

$quizId = (int)($_POST['id'] ?? 0);

if (empty($quizId)) jsonResponse(false, 'Invalid quiz ID');

$sql = "DELETE FROM quizzes WHERE id = ?";
$stmt = executeQuery($sql, 'i', [$quizId]);

if ($stmt) {
    logActivity(getCurrentUser()['id'], 'Delete Quiz', 'LMS', "Deleted quiz ID: $quizId");
    jsonResponse(true, 'Quiz deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete quiz');
}

