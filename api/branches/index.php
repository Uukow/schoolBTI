<?php
/**
 * API Branches Endpoint
 * Get all branches
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

try {
    // Get specific branch if ID provided
    if (isset($_GET['id'])) {
        $branchId = intval($_GET['id']);
        $sql = "SELECT b.*, 
                COUNT(DISTINCT s.id) as total_students,
                COUNT(DISTINCT st.id) as total_staff
                FROM branches b
                LEFT JOIN students s ON b.id = s.branch_id AND s.status = 'Active'
                LEFT JOIN staff st ON b.id = st.branch_id AND st.status = 'Active'
                WHERE b.id = ?
                GROUP BY b.id";
        $stmt = executeQuery($sql, 'i', [$branchId]);
        $branch = fetchOne($stmt);
        
        if (!$branch) {
            sendApiResponse(false, 'Branch not found', null, 404);
        }
        
        sendApiResponse(true, 'Branch retrieved successfully', $branch);
    }
    
    // Get all branches
    $sql = "SELECT b.*, 
            COUNT(DISTINCT s.id) as total_students,
            COUNT(DISTINCT st.id) as total_staff
            FROM branches b
            LEFT JOIN students s ON b.id = s.branch_id AND s.status = 'Active'
            LEFT JOIN staff st ON b.id = st.branch_id AND st.status = 'Active'
            GROUP BY b.id
            ORDER BY b.branch_name ASC";
    
    $result = executeQuery($sql);
    $branches = fetchAll($result);
    
    sendApiResponse(true, 'Branches retrieved successfully', $branches);
    
} catch (Exception $e) {
    sendApiResponse(false, $e->getMessage(), null, 500);
}














