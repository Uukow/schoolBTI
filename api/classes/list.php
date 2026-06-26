<?php
/**
 * Classes API - Get all classes and sections
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check if requesting sections for a specific class
    if (isset($_GET['class_id'])) {
        $classId = intval($_GET['class_id']);
        $sections = getSectionsForClass($classId);
        echo json_encode(['success' => true, 'message' => 'Sections retrieved successfully', 'data' => $sections]);
    } else {
        // Get all classes
        $classes = getAllClasses();
        echo json_encode(['success' => true, 'message' => 'Classes retrieved successfully', 'data' => $classes]);
    }
} catch (Exception $e) {
    error_log("Error in classes API: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getAllClasses() {
    global $conn;
    
    $query = "SELECT 
        c.id,
        c.class_name,
        c.class_code,
        c.class_order,
        c.branch_id,
        b.branch_name,
        c.is_active,
        COUNT(DISTINCT s.id) as total_students,
        COUNT(DISTINCT sec.id) as total_sections
    FROM classes c
    LEFT JOIN branches b ON c.branch_id = b.id
    LEFT JOIN students s ON s.current_class_id = c.id
    LEFT JOIN sections sec ON sec.class_id = c.id
    WHERE c.is_active = 1
    GROUP BY c.id, c.class_name, c.class_code, c.class_order, c.branch_id, b.branch_name, c.is_active
    ORDER BY c.class_order, c.class_name";
    
    $result = $conn->query($query);
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = [
            'id' => (int)$row['id'],
            'class_name' => $row['class_name'],
            'class_code' => $row['class_code'],
            'class_order' => (int)$row['class_order'],
            'branch_id' => (int)$row['branch_id'],
            'branch_name' => $row['branch_name'],
            'is_active' => (bool)$row['is_active'],
            'total_students' => (int)$row['total_students'],
            'total_sections' => (int)$row['total_sections'],
        ];
    }
    
    return $classes;
}

function getSectionsForClass($classId) {
    global $conn;
    
    $query = "SELECT 
        s.id,
        s.section_name,
        s.class_id,
        c.class_name,
        s.capacity,
        COUNT(st.id) as current_students
    FROM sections s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN students st ON st.current_section_id = s.id
    WHERE s.class_id = ? AND s.is_active = 1
    GROUP BY s.id, s.section_name, s.class_id, c.class_name, s.capacity
    ORDER BY s.section_name";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = [
            'id' => (int)$row['id'],
            'section_name' => $row['section_name'],
            'class_id' => (int)$row['class_id'],
            'class_name' => $row['class_name'],
            'capacity' => (int)$row['capacity'],
            'current_students' => (int)$row['current_students'],
        ];
    }
    
    $stmt->close();
    return $sections;
}
