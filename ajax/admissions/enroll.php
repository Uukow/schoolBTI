<?php
/**
 * AJAX: Enroll Student from Application
 * 
 * Convert admission application to student record
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
    jsonResponse(false, 'You do not have permission to enroll students');
}

$applicationId = $_POST['application_id'] ?? 0;

if (empty($applicationId)) {
    jsonResponse(false, 'Invalid application ID');
}

// Begin transaction
beginTransaction();

try {
    // Get application details
    $sql = "SELECT * FROM admission_applications WHERE id = ? AND status = 'Accepted'";
    $stmt = executeQuery($sql, 'i', [$applicationId]);
    $app = fetchOne($stmt);
    
    if (!$app) {
        jsonResponse(false, 'Application not found or not accepted');
    }
    
    // Check if already enrolled
    if ($app['status'] == 'Enrolled') {
        jsonResponse(false, 'Student already enrolled');
    }
    
    // Generate student ID
    $sql = "SELECT MAX(id) as max_id FROM students";
    $result = executeQuery($sql);
    $row = fetchOne($result);
    $nextId = ($row['max_id'] ?? 0) + 1;
    $studentId = generateUniqueId(STUDENT_ID_PREFIX, $nextId, 6);
    
    // Generate admission number
    $admissionNo = 'ADM' . date('Y') . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    
    // Create student record
    $sql = "INSERT INTO students (
        student_id, admission_no, branch_id, first_name, last_name,
        gender, date_of_birth, email, phone, address,
        admission_date, current_class_id, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, 'Active')";
    
    $stmt = executeQuery($sql, 'ssisssssssi', [
        $studentId, $admissionNo, $app['branch_id'], $app['first_name'], $app['last_name'],
        $app['gender'], $app['date_of_birth'], $app['email'], $app['phone'], $app['address'],
        $app['class_id']
    ]);
    
    $studentRecordId = getLastInsertId();
    
    // Create parent record if details provided
    if (!empty($app['parent_name']) && !empty($app['parent_phone'])) {
        $parentSql = "INSERT INTO parents (first_name, last_name, phone, email, address)
                     VALUES (?, '', ?, ?, ?)";
        $parentStmt = executeQuery($parentSql, 'ssss', [
            $app['parent_name'], $app['parent_phone'], $app['parent_email'], $app['address']
        ]);
        
        $parentId = getLastInsertId();
        
        // Link student to parent
        $linkSql = "INSERT INTO student_parents (student_id, parent_id, relationship, is_primary)
                   VALUES (?, ?, 'Parent', 1)";
        executeQuery($linkSql, 'ii', [$studentRecordId, $parentId]);
    }
    
    // Update application status
    $updateSql = "UPDATE admission_applications SET status = 'Enrolled' WHERE id = ?";
    executeQuery($updateSql, 'i', [$applicationId]);
    
    // Log activity
    logActivity(
        getCurrentUser()['id'],
        'Enroll Student',
        'Admissions',
        "Enrolled student from application: {$app['first_name']} {$app['last_name']} (App No: {$app['application_no']})"
    );
    
    commitTransaction();
    jsonResponse(true, 'Student enrolled successfully! Student ID: ' . $studentId);
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

