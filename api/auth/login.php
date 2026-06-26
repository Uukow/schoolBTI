<?php
/**
 * API Login Endpoint
 */

require_once '../config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendApiResponse(false, 'Invalid request method', null, 405);
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// OR try standard POST if JSON failed
if (empty($username)) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
}

// Sanitize
$username = sanitize($username);

if (empty($username) || empty($password)) {
    sendApiResponse(false, 'Username and password are required', null, 400);
}

// Attempt login
// We reuse the loginUser function from functions.php (via config.php -> auth.php)
$result = loginUser($username, $password);

if ($result['success']) {
    $user = getCurrentUser(); // This gets populated by loginUser setting the session
    
    // For API, we might need to return the session ID or a token. 
    // Since this is a simple PHPSESSID based system, we can just return the user data
    // and the client can handle cookies if they support it, 
    // BUT mobile apps often prefer a token. 
    // For now, we'll return user data.
    
    // Check if student
    $isStudent = ($user['role_name'] === 'Student');
    
    // Prepare response data
    $responseData = [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role_name'], // e.g., 'Student', 'Teacher', 'Admin'
        'branch_id' => $user['branch_id'],
        'full_name' => $user['first_name'] . ' ' . $user['last_name'], // Assuming these fields exist, if not we'll check schema later
        'profile_image' => !empty($user['profile_image']) ? APP_URL . $user['profile_image'] : null,
    ];

    // If student, we might want to fetch student specific details immediately or let a subsequent call do it
    if ($isStudent) {
        $studentData = getStudentByUserId($user['id']);
        if ($studentData) {
            $responseData['student_id'] = $studentData['id'];
            $responseData['admission_no'] = $studentData['admission_no'];
        }
    } else {
        // Fetch staff/teacher details
        // Assuming there is a similar function or table
        $teacherData = getTeacherByUserId($user['id']);
         if ($teacherData) {
            $responseData['staff_id'] = $teacherData['id'];
        }
    }

    sendApiResponse(true, 'Login successful', $responseData);

} else {
    sendApiResponse(false, $result['message'], null, 401);
}
