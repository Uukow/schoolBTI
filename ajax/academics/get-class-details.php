<?php
/**
 * AJAX: Get Class Details (Comprehensive)
 * 
 * @author School ERP Development Team
 */

require_once '../../config/config.php';

if (!isLoggedIn()) jsonResponse(false, 'Unauthorized');
if (!hasRole(['Super Admin', 'Admin'])) jsonResponse(false, 'Permission denied');

$classId = $_GET['id'] ?? 0;

if (empty($classId)) {
    jsonResponse(false, 'Invalid class ID');
}

// Get class basic information
$sql = "SELECT c.*, b.branch_name 
        FROM classes c 
        LEFT JOIN branches b ON c.branch_id = b.id 
        WHERE c.id = ?";
$stmt = executeQuery($sql, 'i', [$classId]);
$class = fetchOne($stmt);

if (!$class) {
    jsonResponse(false, 'Class not found');
}

// Get sections
$sectionsSql = "SELECT * FROM sections WHERE class_id = ? AND is_active = 1 ORDER BY section_name";
$sectionsStmt = executeQuery($sectionsSql, 'i', [$classId]);
$sections = fetchAll($sectionsStmt);

// Get student count by section
$studentCountSql = "SELECT current_section_id, COUNT(*) as count 
                    FROM students 
                    WHERE current_class_id = ? AND status = 'Active' 
                    GROUP BY current_section_id";
$studentCountStmt = executeQuery($studentCountSql, 'i', [$classId]);
$studentCounts = fetchAll($studentCountStmt);
$studentCountBySection = [];
foreach ($studentCounts as $sc) {
    $studentCountBySection[$sc['current_section_id']] = $sc['count'];
}

// Get total student count
$totalStudentsSql = "SELECT COUNT(*) as count FROM students WHERE current_class_id = ? AND status = 'Active'";
$totalStudentsStmt = executeQuery($totalStudentsSql, 'i', [$classId]);
$totalStudents = fetchOne($totalStudentsStmt);

// Get subjects assigned to this class (current session)
$currentSession = getCurrentSession();
$subjectsSql = "SELECT cs.*, s.subject_name, s.subject_code, s.subject_type,
                st.first_name as teacher_first_name, st.last_name as teacher_last_name
                FROM class_subjects cs
                INNER JOIN subjects s ON cs.subject_id = s.id
                LEFT JOIN staff st ON cs.teacher_id = st.id
                WHERE cs.class_id = ? AND cs.session_id = ?
                ORDER BY s.subject_name";
$subjectsStmt = executeQuery($subjectsSql, 'ii', [$classId, $currentSession['id']]);
$subjects = fetchAll($subjectsStmt);

$response = [
    'class' => $class,
    'sections' => $sections,
    'studentCountBySection' => $studentCountBySection,
    'totalStudents' => $totalStudents['count'] ?? 0,
    'subjects' => $subjects
];

jsonResponse(true, 'Class details loaded', $response);

