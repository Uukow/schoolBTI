<?php
/**
 * AJAX: Delete Student
 * 
 * Delete a student record
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

// Check permission
if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to delete students');
}

$studentId = $_POST['id'] ?? 0;

if (empty($studentId)) {
    jsonResponse(false, 'Invalid student ID');
}

// Begin transaction
beginTransaction();

try {
    // Get student info for logging
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$studentId]);
    $student = fetchOne($stmt);
    
    if (!$student) {
        jsonResponse(false, 'Student not found');
    }
    
    // Delete student (CASCADE will handle related records)
    $deleteSql = "DELETE FROM students WHERE id = ?";
    $stmt = executeQuery($deleteSql, 'i', [$studentId]);
    
    if ($stmt) {
        // Log activity
        logActivity(
            getCurrentUser()['id'],
            'Delete Student',
            'Students',
            'Deleted student: ' . $student['first_name'] . ' ' . $student['last_name'] . ' (ID: ' . $student['student_id'] . ')'
        );
        
        commitTransaction();
        jsonResponse(true, 'Student deleted successfully');
    } else {
        rollbackTransaction();
        jsonResponse(false, 'Failed to delete student');
    }
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}


