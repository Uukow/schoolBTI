<?php
/**
 * Check Student Account - Simple Diagnostic
 * 
 * Checks if student account exists and password status
 */

defined('ABSPATH') or define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'config/config.php';

$username = $_GET['username'] ?? 'stu000001';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Student Account</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>Student Account Diagnostic</h2>
    <p>Checking username: <strong><?php echo htmlspecialchars($username); ?></strong></p>
    
    <?php
    // Check if user exists
    $sql = "SELECT u.id, u.username, u.email, u.password, u.is_active, u.is_verified, 
                   u.login_attempts, u.locked_until, r.role_name,
                   s.student_id, s.first_name, s.last_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN students s ON u.id = s.user_id
            WHERE LOWER(u.username) = LOWER(?) OR LOWER(u.email) = LOWER(?)";
            
    $stmt = executeQuery($sql, 'ss', [$username, $username]);
    $user = fetchOne($stmt);
    
    if (!$user):
    ?>
        <div class="section error">
            <h3>❌ User Not Found</h3>
            <p>No user found with username/email: <?php echo htmlspecialchars($username); ?></p>
            
            <h4>Available Student Accounts:</h4>
            <?php
            $allSql = "SELECT u.username, u.email, s.student_id, s.first_name, s.last_name
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      LEFT JOIN students s ON u.id = s.user_id
                      WHERE r.role_name = 'Student'
                      ORDER BY u.username";
            $stmt = executeQuery($allSql);
            $all = fetchAll($stmt);
            ?>
            <ul>
            <?php foreach ($all as $stu): ?>
                <li><strong><?php echo htmlspecialchars($stu['username']); ?></strong> - 
                    <?php echo htmlspecialchars($stu['first_name'] . ' ' . $stu['last_name']); ?> 
                    (<?php echo htmlspecialchars($stu['student_id']); ?>)</li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="section success">
            <h3>✅ User Found</h3>
            <pre>
User ID: <?php echo $user['id']; ?>
Username: <?php echo htmlspecialchars($user['username']); ?>
Email: <?php echo htmlspecialchars($user['email']); ?>
Role: <?php echo htmlspecialchars($user['role_name']); ?>
Student ID: <?php echo htmlspecialchars($user['student_id'] ?? 'N/A'); ?>
Name: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
Is Active: <?php echo $user['is_active'] ? 'Yes ✅' : 'No ❌'; ?>
Is Verified: <?php echo $user['is_verified'] ? 'Yes ✅' : 'No ❌'; ?>
Login Attempts: <?php echo $user['login_attempts']; ?>
Locked Until: <?php echo $user['locked_until'] ? htmlspecialchars($user['locked_until']) : 'Not locked ✅'; ?>
            </pre>
        </div>
        
        <?php if ($user['role_name'] !== 'Student'): ?>
        <div class="section error">
            <h3>❌ Wrong Role</h3>
            <p>User role is "<?php echo htmlspecialchars($user['role_name']); ?>" but should be "Student"</p>
        </div>
        <?php endif; ?>
        
        <?php if (!$user['is_active']): ?>
        <div class="section error">
            <h3>❌ Account Inactive</h3>
            <p>The account is not active. Please activate it first.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($user['locked_until'] && strtotime($user['locked_until']) > time()): ?>
        <div class="section error">
            <h3>❌ Account Locked</h3>
            <p>Account is locked until: <?php echo htmlspecialchars($user['locked_until']); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h3>Password Hash Info</h3>
            <pre>
Password Hash (first 30 chars): <?php echo substr($user['password'], 0, 30); ?>...
Hash Length: <?php echo strlen($user['password']); ?> characters
Hash Algorithm: <?php 
    $info = password_get_info($user['password']);
    echo $info['algoName'] ?? 'Unknown';
?>
            </pre>
        </div>
        
        <div class="section">
            <h3>Test Password</h3>
            <form method="POST">
                <p>Enter password to test:</p>
                <input type="password" name="test_password" placeholder="Enter password" style="padding: 8px; width: 300px;">
                <button type="submit" style="padding: 8px 15px; margin-left: 10px;">Test Password</button>
            </form>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_password'])):
                $testPassword = $_POST['test_password'];
                $match = verifyPassword($testPassword, $user['password']);
            ?>
                <div style="margin-top: 15px; padding: 10px; background: <?php echo $match ? '#d4edda' : '#f8d7da'; ?>; border-radius: 5px;">
                    <?php if ($match): ?>
                        <h4 class="success">✅ Password Matches!</h4>
                        <p>The password you entered is correct. Login should work.</p>
                    <?php else: ?>
                        <h4 class="error">❌ Password Does NOT Match</h4>
                        <p>The password you entered does not match the stored password hash.</p>
                        <p><strong>Solution:</strong> Reset the password using the Reset Password button in Students List.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h3>Quick Actions</h3>
            <p>
                <a href="modules/students/list.php" style="display: inline-block; padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Go to Students List</a>
                <a href="student-login.php" style="display: inline-block; padding: 8px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Try Student Login</a>
            </p>
        </div>
    <?php endif; ?>
</body>
</html>

