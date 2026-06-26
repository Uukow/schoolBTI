<?php
/**
 * AJAX: Graduate Class(es) - Bulk Graduation Processing
 * 
 * Processes bulk class graduation with comprehensive state management,
 * transactional integrity, and immutable data protection.
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'Permission denied. Only administrators can graduate classes.');
}

$classIdsJson = $_POST['class_ids'] ?? '[]';
$remarks = sanitize($_POST['remarks'] ?? '');

// Parse class IDs
$classIds = json_decode($classIdsJson, true);

if (!is_array($classIds) || empty($classIds)) {
    jsonResponse(false, 'No classes selected for graduation.');
}

// Validate all class IDs are integers
$classIds = array_filter(array_map('intval', $classIds));
if (empty($classIds)) {
    jsonResponse(false, 'Invalid class IDs provided.');
}

$currentUser = getCurrentUser();
$currentTime = date('Y-m-d H:i:s');

// Begin transaction
beginTransaction();

try {
    $graduatedClasses = [];
    $totalStudentsAffected = 0;
    $errors = [];

    foreach ($classIds as $classId) {
        // Validate class exists and is not already graduated
        $class = getClassGraduationStatus($classId);
        
        if (!$class) {
            $errors[] = "Class ID {$classId} not found.";
            continue;
        }

        if ($class['graduation_status'] === 'Graduated') {
            $errors[] = "Class '{$class['class_name']}' is already graduated.";
            continue;
        }

        // Count active students in this class
        $studentsSql = "SELECT COUNT(*) as count FROM students 
                        WHERE current_class_id = ? AND status = 'Active'";
        $studentsStmt = executeQuery($studentsSql, 'i', [$classId]);
        $studentsCount = fetchOne($studentsStmt);
        $studentCount = $studentsCount['count'] ?? 0;

        // Update class graduation status
        $updateClassSql = "UPDATE classes 
                          SET graduation_status = 'Graduated',
                              graduated_at = ?,
                              graduated_by = ?,
                              graduation_remarks = ?
                          WHERE id = ?";
        
        $updateStmt = executeQuery($updateClassSql, 'sisi', [
            $currentTime,
            $currentUser['id'],
            $remarks,
            $classId
        ]);

        if (!$updateStmt) {
            global $conn;
            throw new Exception("Failed to update class {$classId}: " . ($conn->error ?? 'Unknown error'));
        }

        // Update all active students in this class to "Graduated" status
        if ($studentCount > 0) {
            $updateStudentsSql = "UPDATE students 
                                  SET status = 'Graduated'
                                  WHERE current_class_id = ? AND status = 'Active'";
            
            $updateStudentsStmt = executeQuery($updateStudentsSql, 'i', [$classId]);
            
            if (!$updateStudentsStmt) {
                global $conn;
                throw new Exception("Failed to update students for class {$classId}: " . ($conn->error ?? 'Unknown error'));
            }
        }

        // Log graduation action
        logClassGraduation($classId, 'Graduated', $studentCount, $remarks);

        // Log activity
        logActivity(
            $currentUser['id'],
            'Graduate Class',
            'Academics',
            "Graduated class: {$class['class_name']} (ID: {$classId}) - {$studentCount} students affected"
        );

        $graduatedClasses[] = [
            'id' => $classId,
            'name' => $class['class_name'],
            'students' => $studentCount
        ];

        $totalStudentsAffected += $studentCount;
    }

    // If any errors occurred but some classes were graduated, we need to decide
    // For now, we'll commit if at least one class was successfully graduated
    if (empty($graduatedClasses) && !empty($errors)) {
        rollbackTransaction();
        jsonResponse(false, 'Graduation failed: ' . implode(' ', $errors));
    }

    // Commit transaction
    commitTransaction();

    // Build success message
    $message = '<div class="text-left">';
    $message .= '<h5>Graduation Completed Successfully!</h5>';
    $message .= '<p><strong>Classes Graduated:</strong> ' . count($graduatedClasses) . '</p>';
    $message .= '<p><strong>Total Students Affected:</strong> ' . $totalStudentsAffected . '</p>';
    
    if (!empty($graduatedClasses)) {
        $message .= '<ul class="mt-2">';
        foreach ($graduatedClasses as $class) {
            $message .= '<li><strong>' . htmlspecialchars($class['name']) . '</strong> (' . $class['students'] . ' students)</li>';
        }
        $message .= '</ul>';
    }

    if (!empty($errors)) {
        $message .= '<div class="alert alert-warning mt-3">';
        $message .= '<strong>Warnings:</strong><ul>';
        foreach ($errors as $error) {
            $message .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $message .= '</ul></div>';
    }

    $message .= '<div class="alert alert-info mt-3">';
    $message .= '<strong>Note:</strong> All academic and financial operations for these classes are now permanently disabled.';
    $message .= ' All existing records remain accessible in read-only mode for audit and reporting purposes.';
    $message .= '</div>';
    $message .= '</div>';

    jsonResponse(true, $message, [
        'graduated_count' => count($graduatedClasses),
        'students_affected' => $totalStudentsAffected,
        'classes' => $graduatedClasses,
        'warnings' => $errors
    ]);

} catch (Exception $e) {
    rollbackTransaction();
    
    // Log error
    error_log("Class Graduation Error: " . $e->getMessage());
    
    jsonResponse(false, 'An error occurred during graduation: ' . $e->getMessage());
}

