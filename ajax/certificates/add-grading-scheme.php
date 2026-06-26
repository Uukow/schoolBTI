<?php
/**
 * Add Grading Scheme
 * 
 * AJAX endpoint to create a new grading scheme with grade scale items
 */

require_once '../../config/config.php';

header('Content-Type: application/json');

// Check authentication and authorization
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized access');
}

if (!hasRole(['Super Admin', 'Admin'])) {
    jsonResponse(false, 'You do not have permission to perform this action');
}

try {
    // Validate required fields
    $required = ['scheme_name', 'scale_type', 'max_gpa'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    $schemeName = sanitize($_POST['scheme_name']);
    $scaleType = sanitize($_POST['scale_type']);
    $maxGpa = floatval($_POST['max_gpa']);
    $branchId = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;
    $sessionId = !empty($_POST['session_id']) ? intval($_POST['session_id']) : null;
    $passingPercentage = isset($_POST['passing_percentage']) ? floatval($_POST['passing_percentage']) : 50.00;
    $description = sanitize($_POST['description'] ?? '');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate grades array
    if (empty($_POST['grades']) || !is_array($_POST['grades'])) {
        jsonResponse(false, 'Please add at least one grade level');
    }
    
    global $conn;
    $conn->begin_transaction();
    
    // If setting as default, unset other defaults
    if ($isDefault) {
        $updateSql = "UPDATE grading_schemes SET is_default = 0 WHERE 1=1";
        if ($branchId) {
            $updateSql .= " AND branch_id = $branchId";
        }
        $conn->query($updateSql);
    }
    
    // Insert grading scheme
    $sql = "INSERT INTO grading_schemes (
                scheme_name,
                scale_type,
                max_gpa,
                branch_id,
                session_id,
                passing_percentage,
                description,
                is_default,
                is_active,
                created_by,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Failed to prepare grading scheme insert: ' . $conn->error);
    }

    $userId = $_SESSION['user_id'];

    // Types: s (scheme_name), s (scale_type), d (max_gpa), i (branch_id), i (session_id),
    // d (passing_percentage), s (description), i (is_default), i (created_by)
    $stmt->bind_param(
        'ssdiidsii',
        $schemeName,
        $scaleType,
        $maxGpa,
        $branchId,
        $sessionId,
        $passingPercentage,
        $description,
        $isDefault,
        $userId
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create grading scheme');
    }
    
    $schemeId = $conn->insert_id;
    
    // Insert grade scale items
    $itemSql = "INSERT INTO grading_scale_items (grading_scheme_id, grade_letter, min_percentage, 
                max_percentage, grade_point, description, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $itemStmt = $conn->prepare($itemSql);
    $order = 0;
    
    foreach ($_POST['grades'] as $grade) {
        $gradeLetter = sanitize($grade['grade_letter']);
        $minPercentage = floatval($grade['min_percentage']);
        $maxPercentage = floatval($grade['max_percentage']);
        $gradePoint = floatval($grade['grade_point']);
        $gradeDesc = sanitize($grade['description'] ?? '');
        
        $itemStmt->bind_param('isdddsi', $schemeId, $gradeLetter, $minPercentage, 
                             $maxPercentage, $gradePoint, $gradeDesc, $order);
        
        if (!$itemStmt->execute()) {
            throw new Exception('Failed to add grade level: ' . $gradeLetter);
        }
        
        $order++;
    }
    
    // Log activity
    logActivity($_SESSION['user_id'], 'Create Grading Scheme', 'Certificates', 
                "Created grading scheme: $schemeName");
    
    $conn->commit();
    jsonResponse(true, 'Grading scheme created successfully', ['scheme_id' => $schemeId]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    jsonResponse(false, $e->getMessage());
}

