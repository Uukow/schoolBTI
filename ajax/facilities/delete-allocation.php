<?php
/**
 * AJAX: Delete Hostel Allocation
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$allocationId = (int)($_POST['id'] ?? 0);

if (empty($allocationId)) jsonResponse(false, 'Invalid allocation ID');

beginTransaction();

try {
    // Get allocation details
    $sql = "SELECT * FROM hostel_allocations WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$allocationId]);
    $allocation = fetchOne($stmt);
    
    if (!$allocation) jsonResponse(false, 'Allocation not found');
    
    // Delete allocation
    $deleteSql = "DELETE FROM hostel_allocations WHERE id = ?";
    executeQuery($deleteSql, 'i', [$allocationId]);
    
    // Update room occupied count if was active
    if ($allocation['status'] == 'Active') {
        $roomSql = "UPDATE hostel_rooms SET occupied = occupied - 1 WHERE id = ?";
        executeQuery($roomSql, 'i', [$allocation['room_id']]);
    }
    
    logActivity(getCurrentUser()['id'], 'Delete Allocation', 'Facilities', "Deleted allocation ID: $allocationId");
    
    commitTransaction();
    jsonResponse(true, 'Allocation deleted successfully');
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

