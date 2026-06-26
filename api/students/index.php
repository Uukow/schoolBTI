<?php
/**
 * Students API - Get all students or single student
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get single student by ID
    if (isset($_GET['id'])) {
        $studentId = intval($_GET['id']);
        $student = getStudentById($studentId);
        
        if ($student) {
            echo json_encode(['success' => true, 'message' => 'Student retrieved successfully', 'data' => $student]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
        }
        exit;
    }

    // Get all students with optional filters
    $filters = [
        'status' => $_GET['status'] ?? null,
        'class_id' => isset($_GET['class_id']) ? intval($_GET['class_id']) : null,
        'section_id' => isset($_GET['section_id']) ? intval($_GET['section_id']) : null,
        'search' => $_GET['search'] ?? null,
        'branch_id' => isset($_GET['branch_id']) ? intval($_GET['branch_id']) : null,
    ];

    $students = getAllStudents($filters);
    
    echo json_encode(['success' => true, 'message' => 'Students retrieved successfully', 'data' => $students]);

} catch (Exception $e) {
    error_log("Error in students API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Get all students with optional filters
 */
function getAllStudents($filters = []) {
    global $conn;
    
    $query = "SELECT 
        s.id,
        s.admission_no,
        s.first_name,
        s.last_name,
        s.middle_name,
        s.gender,
        s.date_of_birth,
        s.email,
        s.phone,
        s.photo,
        s.address,
        s.city,
        s.state,
        s.current_class_id,
        c.class_name,
        s.current_section_id,
        sec.section_name,
        s.branch_id,
        b.branch_name,
        s.status,
        s.admission_date,
        NULL as father_name,
        NULL as father_phone,
        NULL as mother_name,
        NULL as mother_phone,
        NULL as guardian_name,
        NULL as guardian_phone,
        NULL as roll_number
    FROM students s
    LEFT JOIN classes c ON s.current_class_id = c.id
    LEFT JOIN sections sec ON s.current_section_id = sec.id
    LEFT JOIN branches b ON s.branch_id = b.id
    WHERE 1=1";

    $params = [];
    $types = "";

    // Apply filters
    if (!empty($filters['status'])) {
        $query .= " AND s.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['class_id'])) {
        $query .= " AND s.current_class_id = ?";
        $params[] = $filters['class_id'];
        $types .= "i";
    }

    if (!empty($filters['section_id'])) {
        $query .= " AND s.current_section_id = ?";
        $params[] = $filters['section_id'];
        $types .= "i";
    }

    if (!empty($filters['branch_id'])) {
        $query .= " AND s.branch_id = ?";
        $params[] = $filters['branch_id'];
        $types .= "i";
    }

    if (!empty($filters['search'])) {
        $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_no LIKE ? OR s.email LIKE ?)";
        $searchTerm = "%" . $filters['search'] . "%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ssss";
    }

    $query .= " ORDER BY s.first_name, s.last_name LIMIT 100";

    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    $stmt->close();
    return $students;
}

/**
 * Get student by ID
 */
function getStudentById($studentId) {
    global $conn;
    
    $query = "SELECT 
        s.*,
        c.class_name,
        sec.section_name,
        b.branch_name
    FROM students s
    LEFT JOIN classes c ON s.current_class_id = c.id
    LEFT JOIN sections sec ON s.current_section_id = sec.id
    LEFT JOIN branches b ON s.branch_id = b.id
    WHERE s.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    return $student;
}
