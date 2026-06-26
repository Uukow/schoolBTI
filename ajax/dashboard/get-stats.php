<?php
/**
 * Get Dashboard Statistics (AJAX)
 * 
 * Returns real-time dashboard statistics
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to access this data');
}

try {
    $currentUser = getCurrentUser();
    $isSuperAdmin = hasRole(['Super Admin']);
    $branchId = $isSuperAdmin ? ($_GET['branch_id'] ?? null) : $currentUser['branch_id'];
    $sessionId = $_GET['session_id'] ?? null;
    $useCache = isset($_GET['cache']) ? (bool)$_GET['cache'] : true;
    
    // Get current session if not provided
    if (!$sessionId) {
        $currentSession = getCurrentSession();
        $sessionId = $currentSession['id'] ?? null;
    }
    
    // Get comprehensive stats
    $stats = getDashboardStats($branchId, $sessionId, $useCache);
    
    // Get additional data
    $stats['activities'] = getDashboardActivities(5, $branchId);
    $stats['top_classes'] = getTopPerformingClasses(5, $branchId, $sessionId);
    $stats['fee_trend'] = getFeeCollectionTrend($branchId);
    $stats['attendance_trend'] = getAttendanceTrend($branchId);
    
    // Get chart data for new visualizations
    $stats['student_status_dist'] = getStudentStatusDistribution($branchId);
    $stats['fee_status_dist'] = getFeeStatusDistribution($branchId);
    $stats['class_wise_dist'] = getClassWiseDistribution($branchId, 10);
    $stats['staff_dist'] = getStaffDistribution($branchId);
    $stats['revenue_vs_outstanding'] = getRevenueVsOutstanding($branchId);
    $stats['exam_performance'] = getExamPerformanceByClass($branchId, $sessionId, 8);
    $stats['performance_metrics'] = getOverallPerformanceMetrics($branchId, $sessionId);
    
    jsonResponse(true, 'Dashboard statistics retrieved successfully', $stats);
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

