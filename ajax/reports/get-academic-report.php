<?php
/**
 * AJAX: Get Academic Report
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
    $reportType = $_GET['report_type'] ?? 'Class Performance';
    $classId = $_GET['class_id'] ?? null;
    $subjectId = $_GET['subject_id'] ?? null;
    $examId = $_GET['exam_id'] ?? null;
    
    $summary = [];
    $details = [];
    
    if ($reportType == 'Class Performance' && $classId) {
        try {
            // Get class performance summary
            $sql = "SELECT 
                    COUNT(DISTINCT sm.student_id) as total_students,
                    AVG(sm.marks_obtained) as avg_marks,
                    MAX(sm.marks_obtained) as max_marks,
                    MIN(sm.marks_obtained) as min_marks
                    FROM student_marks sm
                    JOIN students s ON sm.student_id = s.id
                    WHERE s.class_id = ?";
            $stmt = executeQuery($sql, 'i', [$classId]);
            $summaryData = fetchOne($stmt);
            
            $summary = [
                'total_students' => $summaryData['total_students'] ?? 0,
                'avg_marks' => round($summaryData['avg_marks'] ?? 0, 2),
                'max_marks' => $summaryData['max_marks'] ?? 0,
                'min_marks' => $summaryData['min_marks'] ?? 0,
            ];
            
            // Get top performers
            $topSql = "SELECT s.first_name, s.last_name, AVG(sm.marks_obtained) as avg_marks
                       FROM student_marks sm
                       JOIN students s ON sm.student_id = s.id
                       WHERE s.class_id = ?
                       GROUP BY sm.student_id
                       ORDER BY avg_marks DESC
                       LIMIT 10";
            $topStmt = executeQuery($topSql, 'i', [$classId]);
            $topPerformers = fetchAll($topStmt) ?: [];
            
            foreach ($topPerformers as $performer) {
                $details[] = [
                    'name' => ($performer['first_name'] ?? '') . ' ' . ($performer['last_name'] ?? ''),
                    'description' => 'Average Marks',
                    'value' => round($performer['avg_marks'] ?? 0, 2),
                ];
            }
        } catch (Exception $e) {
            $summary = ['message' => 'No academic data available for this class'];
            $details = [];
        }
    } else {
        $summary = ['message' => 'Select a class to generate report'];
    }
    
    $formatted = [
        'report_type' => $reportType,
        'class_name' => $classId ? getClassName($classId) : null,
        'subject_name' => null,
        'exam_type' => null,
        'report_date' => date('Y-m-d'),
        'summary' => $summary,
        'details' => $details,
    ];
    
    jsonResponse(true, 'Academic report loaded', $formatted);
} catch (Exception $e) {
    jsonResponse(false, 'Failed to load academic report: ' . $e->getMessage());
}

function getClassName($classId) {
    if (!$classId) return null;
    try {
        $sql = "SELECT class_name FROM classes WHERE id = ?";
        $stmt = executeQuery($sql, 'i', [$classId]);
        $class = fetchOne($stmt);
        return $class ? ($class['class_name'] ?? '') : '';
    } catch (Exception $e) {
        return '';
    }
}
