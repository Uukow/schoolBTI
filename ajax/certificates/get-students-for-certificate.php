<?php
/**
 * Get Students for Certificate Generation
 * 
 * Returns eligible students for certificate generation based on class and session
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to perform this action');
}

try {
    $classId = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $sessionId = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
    $branchId = isset($_GET['branch_id']) ? intval($_GET['branch_id']) : 0;
    
    if (!$classId || !$sessionId) {
        jsonResponse(false, 'Class and session are required');
    }
    
    // Get students - simplified query to find students currently in the class
    // Note: We check current_class_id since there's no enrollment history table
    // For historical sessions, you may need to check student_promotions or other records
    
    $sql = "SELECT DISTINCT s.id, s.student_id, s.first_name, s.last_name, s.status,
            CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as full_name,
            c.class_name, sec.section_name,
            s.branch_id,
            (SELECT COUNT(*) FROM student_marks sm 
             INNER JOIN exam_schedule es ON sm.exam_schedule_id = es.id
             INNER JOIN exams e ON es.exam_id = e.id
             WHERE sm.student_id = s.id AND e.session_id = ? AND e.class_id = ?
            ) as has_marks,
            (SELECT COUNT(*) FROM student_attendance sa 
             WHERE sa.student_id = s.id AND sa.class_id = ?
            ) as has_attendance
            FROM students s
            LEFT JOIN classes c ON s.current_class_id = c.id
            LEFT JOIN sections sec ON s.current_section_id = sec.id
            WHERE s.current_class_id = ? AND s.status IN ('Active', 'Graduated')";
    
    if ($branchId) {
        $sql .= " AND s.branch_id = $branchId";
    }
    
    $sql .= " ORDER BY s.first_name, s.last_name";
    
    $stmt = executeQuery($sql, 'iiii', [$sessionId, $classId, $classId, $classId]);
    $students = fetchAll($stmt);
    
    // If no students found with current_class_id, try checking student_promotions for historical data
    if (empty($students)) {
        $sql2 = "SELECT DISTINCT s.id, s.student_id, s.first_name, s.last_name, s.status,
                 CONCAT(s.first_name, ' ', COALESCE(s.last_name, '')) as full_name,
                 c.class_name, sec.section_name,
                 s.branch_id,
                 0 as has_marks,
                 0 as has_attendance
                 FROM students s
                 INNER JOIN student_promotions sp ON s.id = sp.student_id
                 LEFT JOIN classes c ON sp.to_class_id = c.id
                 LEFT JOIN sections sec ON s.current_section_id = sec.id
                 WHERE sp.to_class_id = ? AND sp.to_session_id = ? 
                 AND s.status IN ('Active', 'Graduated')";
        
        if ($branchId) {
            $sql2 .= " AND s.branch_id = $branchId";
        }
        
        $sql2 .= " ORDER BY s.first_name, s.last_name";
        
        $stmt2 = executeQuery($sql2, 'ii', [$classId, $sessionId]);
        $students = fetchAll($stmt2);
    }
    
    if (empty($students)) {
        // Provide more helpful error message
        $classCheck = executeQuery("SELECT class_name FROM classes WHERE id = ?", 'i', [$classId]);
        $classData = fetchOne($classCheck);
        $className = $classData ? $classData['class_name'] : 'selected class';
        
        $sessionCheck = executeQuery("SELECT session_name FROM academic_sessions WHERE id = ?", 'i', [$sessionId]);
        $sessionData = fetchOne($sessionCheck);
        $sessionName = $sessionData ? $sessionData['session_name'] : 'selected session';
        
        $message = "No students found for {$className} in {$sessionName}. ";
        $message .= "Please ensure students are enrolled in this class and have 'Active' or 'Graduated' status.";
        
        jsonResponse(false, $message);
    }
    
    jsonResponse(true, 'Students retrieved successfully', $students);
    
} catch (Exception $e) {
    jsonResponse(false, $e->getMessage());
}

