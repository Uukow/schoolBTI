<?php
/**
 * Common Functions
 * 
 * Utility functions used throughout the application
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

/**
 * Sanitize input data
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    return $data;
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * 
 * @param string $phone Phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhone($phone) {
    return preg_match('/^[+]?[0-9\s\-()]+$/', $phone);
}

/**
 * Hash password securely
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate unique ID with prefix
 * 
 * @param string $prefix Prefix for ID
 * @param int $number Number to append
 * @param int $padding Zero padding length
 * @return string Generated ID
 */
function generateUniqueId($prefix, $number, $padding = 6) {
    return $prefix . str_pad($number, $padding, '0', STR_PAD_LEFT);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date) || $date == '0000-00-00') {
        return '';
    }
    
    return date($format, strtotime($date));
}

/**
 * Format date and time
 * 
 * @param string $datetime DateTime string
 * @param string $format Output format
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') {
        return '';
    }
    
    return date($format, strtotime($datetime));
}

/**
 * Format currency
 * 
 * @param float|null $amount Amount to format
 * @param bool $includeSymbol Include currency symbol
 * @return string Formatted currency
 */
function formatCurrency($amount, $includeSymbol = true) {
    // Handle null values - convert to 0
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    
    // Ensure it's a numeric value
    $amount = (float) $amount;
    
    $formatted = number_format($amount, 2);
    
    if ($includeSymbol) {
        return CURRENCY_SYMBOL . $formatted;
    }
    
    return $formatted;
}

/**
 * Calculate percentage
 * 
 * @param float $obtained Obtained value
 * @param float $total Total value
 * @param int $decimals Decimal places
 * @return float Percentage
 */
function calculatePercentage($obtained, $total, $decimals = 2) {
    if ($total == 0) {
        return 0;
    }
    
    return round(($obtained / $total) * 100, $decimals);
}

/**
 * Calculate GPA from percentage
 * 
 * @param float $percentage Percentage value
 * @return float GPA value
 */
function calculateGPA($percentage) {
    if ($percentage >= 90) return 4.00;
    if ($percentage >= 80) return 3.70;
    if ($percentage >= 75) return 3.30;
    if ($percentage >= 70) return 3.00;
    if ($percentage >= 65) return 2.70;
    if ($percentage >= 60) return 2.30;
    if ($percentage >= 50) return 2.00;
    return 0.00;
}

/**
 * Get grade from percentage
 * 
 * @param float $percentage Percentage value
 * @return string Grade letter
 */
function getGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 75) return 'B+';
    if ($percentage >= 70) return 'B';
    if ($percentage >= 65) return 'C+';
    if ($percentage >= 60) return 'C';
    if ($percentage >= 50) return 'D';
    return 'F';
}

/**
 * Upload file
 * 
 * @param array $file $_FILES array element
 * @param string $uploadDir Upload directory path
 * @param array $allowedTypes Allowed file extensions
 * @return array Result array with 'success' and 'filename' or 'error'
 */
function uploadFile($file, $uploadDir, $allowedTypes = ALLOWED_EXTENSIONS) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] == UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    // Check for upload errors
    if ($file['error'] != UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds limit'];
    }
    
    // Get file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 * 
 * @param string $filepath File path
 * @return bool True on success, false on failure
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get current URL
 * 
 * @return string Current URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Get user IP address
 * 
 * @return string IP address
 */
function getClientIP() {
    $ip = '';
    
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
}

/**
 * Log activity
 * 
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $module Module name
 * @param string $description Description
 * @return bool Success status
 */
function logActivity($userId, $action, $module, $description = '') {
    $sql = "INSERT INTO activity_logs (user_id, action, module, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = executeQuery($sql, 'isssss', [$userId, $action, $module, $description, $ip, $userAgent]);
    
    return $stmt !== false;
}

/**
 * Send JSON response
 * 
 * @param bool $success Success status
 * @param string $message Message
 * @param mixed $data Additional data
 */
function jsonResponse($success, $message, $data = null) {
    // Clear any output buffer to ensure clean JSON response
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Set proper headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get current academic session
 * 
 * @return array|null Current session data
 */
function getCurrentSession() {
    $sql = "SELECT * FROM academic_sessions WHERE is_active = 1 LIMIT 1";
    $stmt = executeQuery($sql);
    return fetchOne($stmt);
}

/**
 * Get student by user ID
 * 
 * @param int $userId User ID
 * @return array|null Student data
 */
function getStudentByUserId($userId) {
    $sql = "SELECT s.* FROM students s 
            WHERE s.user_id = ?";
    $stmt = executeQuery($sql, 'i', [$userId]);
    return fetchOne($stmt);
}

/**
 * Get teacher/staff by user ID
 * 
 * @param int $userId User ID
 * @return array|null Staff/Teacher data
 */
function getTeacherByUserId($userId) {
    $sql = "SELECT s.* FROM staff s 
            WHERE s.user_id = ?";
    $stmt = executeQuery($sql, 'i', [$userId]);
    return fetchOne($stmt);
}

/**
 * Check if user has permission
 * 
 * @param int $userId User ID
 * @param string $permissionKey Permission key
 * @return bool True if has permission, false otherwise
 */
function hasPermission($userId, $permissionKey) {
    $sql = "SELECT COUNT(*) as count FROM role_permissions rp
            INNER JOIN permissions p ON rp.permission_id = p.id
            INNER JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = ? AND p.permission_key = ?";
    
    $stmt = executeQuery($sql, 'is', [$userId, $permissionKey]);
    $result = fetchOne($stmt);
    
    return $result['count'] > 0;
}

/**
 * Generate barcode
 * 
 * @param string $text Text to encode
 * @param string $filepath File path to save
 * @return bool Success status
 */
function generateBarcode($text, $filepath) {
    // This is a placeholder. You would integrate with a barcode library
    // like picqer/php-barcode-generator
    return true;
}

/**
 * Generate QR code
 * 
 * @param string $text Text to encode
 * @param string $filepath File path to save
 * @return bool Success status
 */
function generateQRCode($text, $filepath) {
    // This is a placeholder. You would integrate with a QR code library
    // like endroid/qr-code
    return true;
}

/**
 * Paginate array/data
 * 
 * @param array $data Data to paginate
 * @param int $page Current page
 * @param int $perPage Items per page
 * @return array Paginated data with metadata
 */
function paginate($data, $page = 1, $perPage = RECORDS_PER_PAGE) {
    $total = count($data);
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    
    $offset = ($page - 1) * $perPage;
    $items = array_slice($data, $offset, $perPage);
    
    return [
        'data' => $items,
        'current_page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'from' => $offset + 1,
        'to' => min($offset + $perPage, $total)
    ];
}

/**
 * Get age from date of birth
 * 
 * @param string $dob Date of birth
 * @return int Age in years
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Format file size to human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 B';
    
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    
    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

/**
 * Debug function (only use in development)
 * 
 * @param mixed $data Data to debug
 * @param bool $die Stop execution
 */
function dd($data, $die = true) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    
    if ($die) {
        die();
    }
}

/**
 * Check if a class is graduated
 * 
 * @param int $classId Class ID
 * @return bool True if class is graduated, false otherwise
 */
function isClassGraduated($classId) {
    if (empty($classId)) {
        return false;
    }
    
    $sql = "SELECT graduation_status FROM classes WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$classId]);
    $class = fetchOne($stmt);
    
    return $class && $class['graduation_status'] === 'Graduated';
}

/**
 * Get class graduation status
 * 
 * @param int $classId Class ID
 * @return array|null Class graduation data or null
 */
function getClassGraduationStatus($classId) {
    if (empty($classId)) {
        return null;
    }
    
    $sql = "SELECT id, class_name, graduation_status, graduated_at, graduated_by, graduation_remarks,
            (SELECT username FROM users WHERE id = classes.graduated_by) as graduated_by_username
            FROM classes WHERE id = ?";
    $stmt = executeQuery($sql, 'i', [$classId]);
    return fetchOne($stmt);
}

/**
 * Validate that a class is not graduated before allowing operations
 * 
 * @param int $classId Class ID
 * @param string $operation Operation name for error message
 * @return array ['success' => bool, 'message' => string]
 */
function validateClassNotGraduated($classId, $operation = 'operation') {
    if (empty($classId)) {
        return ['success' => false, 'message' => 'Invalid class ID'];
    }
    
    $class = getClassGraduationStatus($classId);
    
    if (!$class) {
        return ['success' => false, 'message' => 'Class not found'];
    }
    
    if ($class['graduation_status'] === 'Graduated') {
        $graduatedDate = $class['graduated_at'] ? formatDate($class['graduated_at'], 'd-m-Y H:i') : 'Unknown';
        return [
            'success' => false, 
            'message' => "This class was graduated on {$graduatedDate}. {$operation} is not allowed for graduated classes. All academic and financial operations are disabled to maintain data integrity."
        ];
    }
    
    return ['success' => true, 'message' => ''];
}

/**
 * Get all graduated classes
 * 
 * @param int|null $branchId Optional branch filter
 * @return array Array of graduated classes
 */
function getGraduatedClasses($branchId = null) {
    $sql = "SELECT c.*, b.branch_name,
            (SELECT COUNT(*) FROM students s WHERE s.current_class_id = c.id AND s.status = 'Graduated') as graduated_students,
            (SELECT username FROM users WHERE id = c.graduated_by) as graduated_by_username
            FROM classes c
            LEFT JOIN branches b ON c.branch_id = b.id
            WHERE c.graduation_status = 'Graduated'";
    
    $params = [];
    $types = '';
    
    if ($branchId) {
        $sql .= " AND c.branch_id = ?";
        $params[] = $branchId;
        $types = 'i';
    }
    
    $sql .= " ORDER BY c.graduated_at DESC";
    
    $stmt = executeQuery($sql, $types, $params);
    return fetchAll($stmt);
}

/**
 * Log class graduation action
 * 
 * @param int $classId Class ID
 * @param string $action Action type ('Graduated' or 'Reopened')
 * @param int $studentsAffected Number of students affected
 * @param string|null $remarks Optional remarks
 * @return bool Success status
 */
function logClassGraduation($classId, $action, $studentsAffected = 0, $remarks = null) {
    $currentUser = getCurrentUser();
    if (!$currentUser) {
        return false;
    }
    
    $sql = "INSERT INTO class_graduation_logs (class_id, action, students_affected, performed_by, remarks, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Type string for 7 parameters: i=class_id, s=action, i=students_affected, i=performed_by, s=remarks, s=ip_address, s=user_agent
    // Parameters: [int, string, int, int, string, string, string] = 7 characters needed
    $typeString = 'i' . 's' . 'i' . 'i' . 's' . 's' . 's'; // Explicitly construct 7-char string
    $stmt = executeQuery($sql, $typeString, [
        $classId,              // i - int
        $action,               // s - string
        $studentsAffected,     // i - int
        $currentUser['id'],    // i - int
        $remarks,              // s - string
        $ip,                   // s - string
        $userAgent             // s - string
    ]);
    
    return $stmt !== false;
}


