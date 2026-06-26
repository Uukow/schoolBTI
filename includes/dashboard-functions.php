<?php
/**
 * Dashboard Helper Functions
 * 
 * Optimized functions for dashboard metrics with caching support
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Get dashboard cache key
 */
function getDashboardCacheKey($key, $branchId = null, $sessionId = null) {
    $parts = ['dashboard', $key];
    if ($branchId) $parts[] = 'branch_' . $branchId;
    if ($sessionId) $parts[] = 'session_' . $sessionId;
    return implode('_', $parts);
}

/**
 * Cache dashboard data
 */
function cacheDashboardData($key, $data, $ttl = 300) {
    // Simple file-based cache (can be upgraded to Redis/Memcached)
    $cacheDir = ABSPATH . 'cache/dashboard/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . md5($key) . '.json';
    $cacheData = [
        'data' => $data,
        'expires' => time() + $ttl,
        'created' => time()
    ];
    
    file_put_contents($cacheFile, json_encode($cacheData));
}

/**
 * Get cached dashboard data
 */
function getCachedDashboardData($key) {
    $cacheDir = ABSPATH . 'cache/dashboard/';
    $cacheFile = $cacheDir . md5($key) . '.json';
    
    if (!file_exists($cacheFile)) {
        return null;
    }
    
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    
    if (!$cacheData || $cacheData['expires'] < time()) {
        @unlink($cacheFile);
        return null;
    }
    
    return $cacheData['data'];
}

/**
 * Get comprehensive dashboard statistics
 */
function getDashboardStats($branchId = null, $sessionId = null, $useCache = true) {
    $cacheKey = getDashboardCacheKey('stats', $branchId, $sessionId);
    
    if ($useCache) {
        $cached = getCachedDashboardData($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
    }
    
    $branchFilter = $branchId ? " AND branch_id = " . intval($branchId) : '';
    $sessionFilter = $sessionId ? " AND session_id = " . intval($sessionId) : '';
    
    $stats = [];
    
    // 1. STUDENT METRICS
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'Graduated' THEN 1 ELSE 0 END) as graduated,
            SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive
            FROM students WHERE 1=1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['students'] = [
        'total' => $result['total'] ?? 0,
        'active' => $result['active'] ?? 0,
        'graduated' => $result['graduated'] ?? 0,
        'inactive' => $result['inactive'] ?? 0
    ];
    
    // 2. STAFF METRICS
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN employment_type = 'Teacher' THEN 1 ELSE 0 END) as teachers,
            SUM(CASE WHEN employment_type != 'Teacher' THEN 1 ELSE 0 END) as staff
            FROM staff WHERE 1=1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['staff'] = [
        'total' => $result['total'] ?? 0,
        'active' => $result['active'] ?? 0,
        'teachers' => $result['teachers'] ?? 0,
        'staff' => $result['staff'] ?? 0
    ];
    
    // 3. CLASS METRICS
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_active = 1 AND (graduation_status IS NULL OR graduation_status != 'Graduated') THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN graduation_status = 'Graduated' THEN 1 ELSE 0 END) as graduated
            FROM classes WHERE 1=1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['classes'] = [
        'total' => $result['total'] ?? 0,
        'active' => $result['active'] ?? 0,
        'graduated' => $result['graduated'] ?? 0
    ];
    
    // 4. ATTENDANCE METRICS (Today)
    $attendanceBranchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    $sql = "SELECT 
            COUNT(DISTINCT sa.student_id) as total_students,
            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late
            FROM student_attendance sa
            INNER JOIN students s ON sa.student_id = s.id
            WHERE sa.attendance_date = CURDATE() $attendanceBranchFilter";
    $result = fetchOne(executeQuery($sql));
    $total = $result['total_students'] ?? 0;
    $present = $result['present'] ?? 0;
    $stats['attendance_today'] = [
        'total' => $total,
        'present' => $present,
        'absent' => $result['absent'] ?? 0,
        'late' => $result['late'] ?? 0,
        'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0
    ];
    
    // 5. ATTENDANCE METRICS (This Month)
    $sql = "SELECT 
            COUNT(DISTINCT sa.student_id) as total_students,
            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present,
            COUNT(*) as total_records
            FROM student_attendance sa
            INNER JOIN students s ON sa.student_id = s.id
            WHERE MONTH(sa.attendance_date) = MONTH(CURRENT_DATE())
            AND YEAR(sa.attendance_date) = YEAR(CURRENT_DATE()) $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $monthTotal = $result['total_records'] ?? 0;
    $monthPresent = $result['present'] ?? 0;
    $stats['attendance_month'] = [
        'total_records' => $monthTotal,
        'present' => $monthPresent,
        'percentage' => $monthTotal > 0 ? round(($monthPresent / $monthTotal) * 100, 1) : 0
    ];
    
    // 6. FINANCIAL METRICS
    // Monthly Revenue
    $sql = "SELECT COALESCE(SUM(amount), 0) as total
            FROM fee_payments 
            WHERE MONTH(payment_date) = MONTH(CURRENT_DATE())
            AND YEAR(payment_date) = YEAR(CURRENT_DATE())";
    if ($branchId) {
        $sql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $stats['revenue_month'] = $result['total'] ?? 0;
    
    // Outstanding Fees
    $sql = "SELECT COALESCE(SUM(due_amount), 0) as total
            FROM fee_invoices 
            WHERE status IN ('Unpaid', 'Partially Paid', 'Overdue')";
    if ($branchId) {
        $sql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $stats['outstanding_fees'] = $result['total'] ?? 0;
    
    // Paid vs Unpaid
    $sql = "SELECT 
            COALESCE(SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END), 0) as paid,
            COALESCE(SUM(CASE WHEN status IN ('Unpaid', 'Partially Paid', 'Overdue') THEN total_amount ELSE 0 END), 0) as unpaid
            FROM fee_invoices";
    if ($branchId) {
        $sql .= " WHERE student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $stats['fees'] = [
        'paid' => $result['paid'] ?? 0,
        'unpaid' => $result['unpaid'] ?? 0,
        'total' => ($result['paid'] ?? 0) + ($result['unpaid'] ?? 0)
    ];
    
    // Discounts Given
    $sql = "SELECT COALESCE(SUM(discount), 0) as total
            FROM fee_invoices 
            WHERE discount > 0
            AND MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    if ($branchId) {
        $sql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $stats['discounts_month'] = $result['total'] ?? 0;
    
    // 7. ACADEMIC METRICS
    // Active Classes
    $sql = "SELECT COUNT(*) as count
            FROM classes 
            WHERE is_active = 1 
            AND (graduation_status IS NULL OR graduation_status != 'Graduated') $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['active_classes'] = $result['count'] ?? 0;
    
    // Subjects Taught Today
    $dayOfWeek = intval(date('w')); // 0 = Sunday, 1 = Monday, etc.
    $sql = "SELECT COUNT(DISTINCT tt.subject_id) as count
            FROM timetable tt
            INNER JOIN classes c ON tt.class_id = c.id
            WHERE tt.day_of_week = " . intval($dayOfWeek) . "
            AND c.is_active = 1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['subjects_today'] = $result['count'] ?? 0;
    
    // Attendance Completion Status
    $classesBranchFilter = $branchId ? " AND c.branch_id = " . intval($branchId) : '';
    $sql = "SELECT 
            COUNT(DISTINCT c.id) as total_classes,
            COUNT(DISTINCT CASE WHEN sa.id IS NOT NULL THEN c.id END) as classes_with_attendance
            FROM classes c
            LEFT JOIN student_attendance sa ON sa.class_id = c.id AND sa.attendance_date = CURDATE()
            WHERE c.is_active = 1 $classesBranchFilter";
    $result = fetchOne(executeQuery($sql));
    $totalClasses = $result['total_classes'] ?? 0;
    $classesWithAttendance = $result['classes_with_attendance'] ?? 0;
    $stats['attendance_completion'] = [
        'total_classes' => $totalClasses,
        'completed' => $classesWithAttendance,
        'pending' => $totalClasses - $classesWithAttendance,
        'percentage' => $totalClasses > 0 ? round(($classesWithAttendance / $totalClasses) * 100, 1) : 0
    ];
    
    // Exam Progress
    $currentSession = getCurrentSession();
    if ($currentSession) {
        $branchJoin = $branchId ? " INNER JOIN classes c ON e.class_id = c.id" : "";
        $branchWhere = $branchId ? " AND c.branch_id = " . intval($branchId) : "";
        $sql = "SELECT 
                COUNT(*) as total_exams,
                SUM(CASE WHEN e.end_date < CURDATE() THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN e.start_date <= CURDATE() AND e.end_date >= CURDATE() THEN 1 ELSE 0 END) as ongoing
                FROM exams e
                $branchJoin
                WHERE e.session_id = {$currentSession['id']} $branchWhere";
        $result = fetchOne(executeQuery($sql));
        $stats['exams'] = [
            'total' => $result['total_exams'] ?? 0,
            'completed' => $result['completed'] ?? 0,
            'ongoing' => $result['ongoing'] ?? 0,
            'upcoming' => ($result['total_exams'] ?? 0) - ($result['completed'] ?? 0) - ($result['ongoing'] ?? 0)
        ];
    } else {
        $stats['exams'] = ['total' => 0, 'completed' => 0, 'ongoing' => 0, 'upcoming' => 0];
    }
    
    // Assignment Progress
    $branchJoin = $branchId ? " INNER JOIN classes c ON a.class_id = c.id" : "";
    $branchWhere = $branchId ? " AND c.branch_id = " . intval($branchId) : "";
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN a.due_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN a.due_date >= CURDATE() THEN 1 ELSE 0 END) as pending
            FROM assignments a
            $branchJoin
            WHERE 1=1 $branchWhere";
    $result = fetchOne(executeQuery($sql));
    $stats['assignments'] = [
        'total' => $result['total'] ?? 0,
        'overdue' => $result['overdue'] ?? 0,
        'pending' => $result['pending'] ?? 0
    ];
    
    // 8. ADMINISTRATIVE ALERTS
    // Pending Admissions
    $sql = "SELECT COUNT(*) as count
            FROM admission_applications 
            WHERE status IN ('Pending', 'Under Review') $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['pending_admissions'] = $result['count'] ?? 0;
    
    // Low Data Completion (students without photos)
    $sql = "SELECT COUNT(*) as count
            FROM students 
            WHERE (photo IS NULL OR photo = '') AND status = 'Active' $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['incomplete_profiles'] = $result['count'] ?? 0;
    
    // Overdue Fee Invoices
    $sql = "SELECT COUNT(*) as count
            FROM fee_invoices 
            WHERE status = 'Overdue'";
    if ($branchId) {
        $sql .= " AND student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $stats['overdue_invoices'] = $result['count'] ?? 0;
    
    // Support Tickets
    $branchJoin = $branchId ? " INNER JOIN users u ON st.user_id = u.id" : "";
    $branchWhere = $branchId ? " AND u.branch_id = " . intval($branchId) : "";
    $sql = "SELECT COUNT(*) as count
            FROM support_tickets st
            $branchJoin
            WHERE st.status IN ('Open', 'In Progress') $branchWhere";
    $result = fetchOne(executeQuery($sql));
    $stats['open_tickets'] = $result['count'] ?? 0;
    
    // 9. PAYROLL STATUS
    $sql = "SELECT 
            COUNT(*) as total_staff,
            SUM(CASE WHEN sp.id IS NOT NULL THEN 1 ELSE 0 END) as paid_this_month
            FROM staff s
            LEFT JOIN salary_payments sp ON sp.staff_id = s.id 
            AND MONTH(sp.payment_date) = MONTH(CURRENT_DATE())
            AND YEAR(sp.payment_date) = YEAR(CURRENT_DATE())
            WHERE s.status = 'Active' $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $stats['payroll'] = [
        'total_staff' => $result['total_staff'] ?? 0,
        'paid' => $result['paid_this_month'] ?? 0,
        'pending' => ($result['total_staff'] ?? 0) - ($result['paid_this_month'] ?? 0)
    ];
    
    // Cache the results
    cacheDashboardData($cacheKey, $stats, 300); // 5 minutes cache
    
    return $stats;
}

/**
 * Get recent activities for dashboard
 */
function getDashboardActivities($limit = 10, $branchId = null) {
    // Filter by branch through user's branch_id
    $branchFilter = '';
    if ($branchId) {
        $branchFilter = " AND u.branch_id = " . intval($branchId);
    }
    
    $sql = "SELECT al.*, u.username, u.branch_id, b.branch_name
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE 1=1 $branchFilter
            ORDER BY al.created_at DESC
            LIMIT $limit";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get top performing classes
 */
function getTopPerformingClasses($limit = 5, $branchId = null, $sessionId = null) {
    $branchFilter = $branchId ? " AND c.branch_id = " . intval($branchId) : '';
    $sessionFilter = $sessionId ? " AND e.session_id = " . intval($sessionId) : '';
    
    $sql = "SELECT 
            c.id, c.class_name, c.class_code,
            COUNT(DISTINCT sm.student_id) as students_count,
            AVG(sm.marks_obtained / NULLIF(es.total_marks, 0) * 100) as avg_percentage
            FROM classes c
            INNER JOIN exams e ON e.class_id = c.id
            INNER JOIN exam_schedule es ON es.exam_id = e.id
            INNER JOIN student_marks sm ON sm.exam_schedule_id = es.id
            WHERE c.is_active = 1 $branchFilter $sessionFilter
            GROUP BY c.id, c.class_name, c.class_code
            HAVING avg_percentage IS NOT NULL
            ORDER BY avg_percentage DESC
            LIMIT $limit";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get fee collection trend (last 6 months)
 */
function getFeeCollectionTrend($branchId = null) {
    $branchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    
    $sql = "SELECT 
            DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
            SUM(fp.amount) as total
            FROM fee_payments fp
            INNER JOIN students s ON fp.student_id = s.id
            WHERE fp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            $branchFilter
            GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m')
            ORDER BY month ASC";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get attendance trend (last 30 days)
 */
function getAttendanceTrend($branchId = null) {
    $branchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    
    $sql = "SELECT 
            sa.attendance_date as date,
            COUNT(*) as total,
            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present
            FROM student_attendance sa
            INNER JOIN students s ON sa.student_id = s.id
            WHERE sa.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            $branchFilter
            GROUP BY sa.attendance_date
            ORDER BY sa.attendance_date ASC";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get student status distribution for pie/doughnut chart
 */
function getStudentStatusDistribution($branchId = null) {
    $branchFilter = $branchId ? " AND branch_id = " . intval($branchId) : '';
    
    $sql = "SELECT 
            status,
            COUNT(*) as count
            FROM students
            WHERE 1=1 $branchFilter
            GROUP BY status
            ORDER BY count DESC";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get fee payment status distribution
 */
function getFeeStatusDistribution($branchId = null) {
    $branchFilter = $branchId ? " AND student_id IN (SELECT id FROM students WHERE branch_id = " . intval($branchId) . ")" : '';
    
    $sql = "SELECT 
            status,
            COUNT(*) as count,
            COALESCE(SUM(total_amount), 0) as total_amount
            FROM fee_invoices
            WHERE 1=1 $branchFilter
            GROUP BY status
            ORDER BY count DESC";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get class-wise student distribution
 */
function getClassWiseDistribution($branchId = null, $limit = 10) {
    $branchFilter = $branchId ? " AND c.branch_id = " . intval($branchId) : '';
    $studentBranchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    
    $sql = "SELECT 
            c.class_name,
            COUNT(DISTINCT s.id) as student_count
            FROM classes c
            LEFT JOIN students s ON s.current_class_id = c.id AND s.status = 'Active' $studentBranchFilter
            WHERE c.is_active = 1 $branchFilter
            GROUP BY c.id, c.class_name
            ORDER BY student_count DESC
            LIMIT $limit";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get staff distribution by type
 */
function getStaffDistribution($branchId = null) {
    $branchFilter = $branchId ? " AND branch_id = " . intval($branchId) : '';
    
    $sql = "SELECT 
            employment_type,
            COUNT(*) as count
            FROM staff
            WHERE status = 'Active' $branchFilter
            GROUP BY employment_type
            ORDER BY count DESC";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get monthly revenue vs outstanding fees (last 6 months)
 */
function getRevenueVsOutstanding($branchId = null) {
    $branchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    
    // Revenue data
    $revenueSql = "SELECT 
            DATE_FORMAT(fp.payment_date, '%Y-%m') as month,
            SUM(fp.amount) as revenue
            FROM fee_payments fp
            INNER JOIN students s ON fp.student_id = s.id
            WHERE fp.payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            $branchFilter
            GROUP BY DATE_FORMAT(fp.payment_date, '%Y-%m')
            ORDER BY month ASC";
    
    $revenueData = fetchAll(executeQuery($revenueSql));
    
    // Outstanding fees data
    $outstandingSql = "SELECT 
            DATE_FORMAT(fi.created_at, '%Y-%m') as month,
            SUM(fi.due_amount) as outstanding
            FROM fee_invoices fi
            INNER JOIN students s ON fi.student_id = s.id
            WHERE fi.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            AND fi.status IN ('Unpaid', 'Partially Paid', 'Overdue')
            $branchFilter
            GROUP BY DATE_FORMAT(fi.created_at, '%Y-%m')
            ORDER BY month ASC";
    
    $outstandingData = fetchAll(executeQuery($outstandingSql));
    
    return [
        'revenue' => $revenueData,
        'outstanding' => $outstandingData
    ];
}

/**
 * Get exam performance by class
 */
function getExamPerformanceByClass($branchId = null, $sessionId = null, $limit = 8) {
    $branchFilter = $branchId ? " AND c.branch_id = " . intval($branchId) : '';
    $sessionFilter = $sessionId ? " AND e.session_id = " . intval($sessionId) : '';
    
    $sql = "SELECT 
            c.class_name,
            COUNT(DISTINCT sm.student_id) as students_count,
            AVG((sm.marks_obtained / NULLIF(es.total_marks, 0)) * 100) as avg_percentage,
            MAX((sm.marks_obtained / NULLIF(es.total_marks, 0)) * 100) as max_percentage,
            MIN((sm.marks_obtained / NULLIF(es.total_marks, 0)) * 100) as min_percentage
            FROM classes c
            INNER JOIN exams e ON e.class_id = c.id
            INNER JOIN exam_schedule es ON es.exam_id = e.id
            INNER JOIN student_marks sm ON sm.exam_schedule_id = es.id
            WHERE c.is_active = 1 $branchFilter $sessionFilter
            GROUP BY c.id, c.class_name
            HAVING avg_percentage IS NOT NULL
            ORDER BY avg_percentage DESC
            LIMIT $limit";
    
    return fetchAll(executeQuery($sql));
}

/**
 * Get overall performance metrics for radar chart
 */
function getOverallPerformanceMetrics($branchId = null, $sessionId = null) {
    $branchFilter = $branchId ? " AND branch_id = " . intval($branchId) : '';
    $sessionFilter = $sessionId ? " AND session_id = " . intval($sessionId) : '';
    
    $metrics = [];
    
    // Attendance Rate
    $attendanceBranchFilter = $branchId ? " AND s.branch_id = " . intval($branchId) : '';
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present
            FROM student_attendance sa
            INNER JOIN students s ON sa.student_id = s.id
            WHERE sa.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            $attendanceBranchFilter";
    $result = fetchOne(executeQuery($sql));
    $metrics['attendance'] = $result['total'] > 0 ? round(($result['present'] / $result['total']) * 100, 1) : 0;
    
    // Fee Collection Rate
    $sql = "SELECT 
            COALESCE(SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END), 0) as paid,
            COALESCE(SUM(total_amount), 0) as total
            FROM fee_invoices";
    if ($branchId) {
        $sql .= " WHERE student_id IN (SELECT id FROM students WHERE branch_id = $branchId)";
    }
    $result = fetchOne(executeQuery($sql));
    $metrics['fee_collection'] = $result['total'] > 0 ? round(($result['paid'] / $result['total']) * 100, 1) : 0;
    
    // Academic Performance
    $branchJoin = $branchId ? " INNER JOIN classes c ON e.class_id = c.id" : "";
    $branchWhere = $branchId ? " AND c.branch_id = " . intval($branchId) : "";
    $sql = "SELECT 
            AVG((sm.marks_obtained / NULLIF(es.total_marks, 0)) * 100) as avg_performance
            FROM student_marks sm
            INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
            INNER JOIN exams e ON es.exam_id = e.id
            $branchJoin
            WHERE 1=1 $branchWhere $sessionFilter";
    $result = fetchOne(executeQuery($sql));
    $metrics['academic'] = round($result['avg_performance'] ?? 0, 1);
    
    // Staff Satisfaction (based on active staff ratio)
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active
            FROM staff WHERE 1=1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $metrics['staff_satisfaction'] = $result['total'] > 0 ? round(($result['active'] / $result['total']) * 100, 1) : 0;
    
    // Student Retention
    $sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active
            FROM students WHERE 1=1 $branchFilter";
    $result = fetchOne(executeQuery($sql));
    $metrics['student_retention'] = $result['total'] > 0 ? round(($result['active'] / $result['total']) * 100, 1) : 0;
    
    // Operational Efficiency (based on attendance completion)
    $classesBranchFilter = $branchId ? " AND c.branch_id = " . intval($branchId) : '';
    $sql = "SELECT 
            COUNT(DISTINCT c.id) as total_classes,
            COUNT(DISTINCT CASE WHEN sa.id IS NOT NULL THEN c.id END) as classes_with_attendance
            FROM classes c
            LEFT JOIN student_attendance sa ON sa.class_id = c.id AND sa.attendance_date = CURDATE()
            WHERE c.is_active = 1 $classesBranchFilter";
    $result = fetchOne(executeQuery($sql));
    $metrics['operational_efficiency'] = $result['total_classes'] > 0 ? round(($result['classes_with_attendance'] / $result['total_classes']) * 100, 1) : 0;
    
    return $metrics;
}

/**
 * Clear dashboard cache
 */
function clearDashboardCache($key = null) {
    $cacheDir = ABSPATH . 'cache/dashboard/';
    
    if ($key) {
        $cacheFile = $cacheDir . md5($key) . '.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    } else {
        // Clear all dashboard cache
        $files = glob($cacheDir . '*.json');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

