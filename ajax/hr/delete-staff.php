<?php
/**
 * AJAX: Delete Staff
 * 
 * Delete a staff record
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
    jsonResponse(false, 'You do not have permission to delete staff');
}

$staffId = $_POST['id'] ?? 0;

if (empty($staffId)) {
    jsonResponse(false, 'Invalid staff ID');
}

// Get current user for branch check
$currentUser = getCurrentUser();

// Begin transaction
beginTransaction();

try {
    // Get staff info for logging and branch check
    $sql = "SELECT * FROM staff WHERE id = ?";
    $params = [$staffId];
    $types = 'i';
    
    // Branch filter for non-super admins
    if (!hasRole(['Super Admin'])) {
        $sql .= " AND branch_id = ?";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $stmt = executeQuery($sql, $types, $params);
    $staff = fetchOne($stmt);
    
    if (!$staff) {
        jsonResponse(false, 'Staff not found or access denied');
    }
    
    // Check if staff has associated user account
    if ($staff['user_id']) {
        // Optionally, you might want to delete or deactivate the user account too
        // For now, we'll just delete the staff record and leave the user account
        // You can uncomment the following if you want to delete the user account as well:
        // $deleteUserSql = "DELETE FROM users WHERE id = ?";
        // executeQuery($deleteUserSql, 'i', [$staff['user_id']]);
    }
    
    // Delete staff photo if exists
    if ($staff['photo'] && file_exists(ABSPATH . $staff['photo'])) {
        deleteFile(ABSPATH . $staff['photo']);
    }
    
    // Delete staff record
    $deleteSql = "DELETE FROM staff WHERE id = ?";
    $deleteStmt = executeQuery($deleteSql, 'i', [$staffId]);
    
    if ($deleteStmt) {
        // Log activity
        logActivity(
            getCurrentUser()['id'],
            'Delete Staff',
            'HR',
            'Deleted staff: ' . $staff['first_name'] . ' ' . $staff['last_name'] . ' (ID: ' . $staff['staff_id'] . ')'
        );
        
        commitTransaction();
        jsonResponse(true, 'Staff deleted successfully');
    } else {
        rollbackTransaction();
        jsonResponse(false, 'Failed to delete staff');
    }
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}


