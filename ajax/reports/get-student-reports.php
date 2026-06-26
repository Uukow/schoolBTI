<?php
/**
 * AJAX: Get Student Reports
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

ob_start();
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$currentUser = null;
$userId = $_GET['user_id'] ?? $_POST['user_id'] ?? null;

if ($userId) {
    $sql = "SELECT u.*, r.role_name, b.branch_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id 
            WHERE u.id = ? AND u.is_active = 1";
    $stmt = executeQuery($sql, 'i', [$userId]);
    $currentUser = fetchOne($stmt);
    
    if (!$currentUser) {
        jsonResponse(false, 'Invalid user ID');
    }
} else {
    if (!isLoggedIn()) {
        jsonResponse(false, 'Unauthorized');
    }
    $currentUser = getCurrentUser();
}

try {
    $studentId = $_GET['student_id'] ?? null;
    $classId = $_GET['class_id'] ?? null;
    $sectionId = $_GET['section_id'] ?? null;
    $status = $_GET['status'] ?? null;
    
    $sql = "SELECT s.*, 
            c.class_name,
            sec.section_name,
            p.first_name as parent_first_name,
            p.last_name as parent_last_name,
            p.phone as parent_phone,
            p.email as parent_email
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            LEFT JOIN student_parents sp ON s.id = sp.student_id AND sp.is_primary = 1
            LEFT JOIN parents p ON sp.parent_id = p.id
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if ($studentId) {
        $sql .= " AND s.id = ?";
        $params[] = $studentId;
        $types .= 'i';
    }
    
    if ($classId) {
        $sql .= " AND s.current_class_id = ?";
        $params[] = $classId;
        $types .= 'i';
    }
    
    if ($sectionId) {
        $sql .= " AND s.current_section_id = ?";
        $params[] = $sectionId;
        $types .= 'i';
    }
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    // Branch filter
    if (!hasRole(['Super Admin']) && isset($currentUser['branch_id'])) {
        $sql .= " AND (s.branch_id IS NULL OR s.branch_id = ?)";
        $params[] = $currentUser['branch_id'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY s.admission_number ASC";
    
    $stmt = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    $students = fetchAll($stmt) ?: [];
    
    $formatted = [];
    foreach ($students as $student) {
        // Get academic summary (average marks, total exams)
        $academicData = ['total_exams' => 0, 'avg_marks' => 0];
        try {
            $academicSql = "SELECT 
                            COUNT(DISTINCT exam_id) as total_exams,
                            AVG(marks_obtained) as avg_marks
                            FROM student_marks 
                            WHERE student_id = ?";
            $academicStmt = executeQuery($academicSql, 'i', [$student['id']]);
            $academicData = fetchOne($academicStmt) ?: $academicData;
        } catch (Exception $e) {
            // Table might not exist, use defaults
        }
        
        // Get attendance summary
        $attendanceData = ['total_days' => 0, 'present_days' => 0];
        try {
            $attendanceSql = "SELECT 
                              COUNT(*) as total_days,
                              SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_days
                              FROM student_attendance 
                              WHERE student_id = ?";
            $attendanceStmt = executeQuery($attendanceSql, 'i', [$student['id']]);
            $attendanceData = fetchOne($attendanceStmt) ?: $attendanceData;
        } catch (Exception $e) {
            // Table might not exist, use defaults
        }
        
        $parentName = null;
        if (!empty($student['parent_first_name']) || !empty($student['parent_last_name'])) {
            $parentName = trim(($student['parent_first_name'] ?? '') . ' ' . ($student['parent_last_name'] ?? ''));
        }
        
        $formatted[] = [
            'id' => $student['id'],
            'student_id' => $student['id'],
            'full_name' => ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''),
            'student_name' => ($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''),
            'admission_number' => $student['admission_number'] ?? $student['admission_no'] ?? '',
            'class_name' => $student['class_name'] ?? '',
            'section_name' => $student['section_name'] ?? '',
            'parent_name' => $parentName ?: null,
            'parent_phone' => $student['parent_phone'] ?? null,
            'parent_email' => $student['parent_email'] ?? null,
            'admission_date' => $student['admission_date'] ?? '',
            'date_of_birth' => $student['date_of_birth'] ?? null,
            'gender' => $student['gender'] ?? null,
            'address' => $student['address'] ?? null,
            'status' => $student['status'] ?? 'Active',
            'academic_summary' => [
                'total_exams' => $academicData['total_exams'] ?? 0,
                'avg_marks' => round($academicData['avg_marks'] ?? 0, 2),
            ],
            'attendance_summary' => [
                'total_days' => $attendanceData['total_days'] ?? 0,
                'present_days' => $attendanceData['present_days'] ?? 0,
                'attendance_percentage' => ($attendanceData['total_days'] ?? 0) > 0 
                    ? round((($attendanceData['present_days'] ?? 0) / ($attendanceData['total_days'] ?? 1)) * 100, 2)
                    : 0,
            ],
        ];
    }
    
    jsonResponse(true, 'Student reports loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load student reports: ' . $e->getMessage());
}
