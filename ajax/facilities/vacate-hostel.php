<?php
/**
 * AJAX: Vacate Hostel Room
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
    if ($allocation['status'] == 'Vacated') jsonResponse(false, 'Already vacated');
    
    // Update allocation
    $updateSql = "UPDATE hostel_allocations 
                  SET status = 'Vacated', vacation_date = CURDATE()
                  WHERE id = ?";
    executeQuery($updateSql, 'i', [$allocationId]);
    
    // Update room occupied count
    $roomSql = "UPDATE hostel_rooms SET occupied = occupied - 1 WHERE id = ?";
    executeQuery($roomSql, 'i', [$allocation['room_id']]);
    
    logActivity(getCurrentUser()['id'], 'Vacate Hostel', 'Facilities', "Vacated allocation ID: $allocationId");
    
    commitTransaction();
    jsonResponse(true, 'Room vacated successfully!');
    
} catch (Exception $e) {
    rollbackTransaction();
    jsonResponse(false, 'Error: ' . $e->getMessage());
}

