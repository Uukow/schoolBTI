<?php
/**
 * Login Debug Script
 * 
 * Tests login components to identify issues
 * Remove after fixing
 */

defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once ABSPATH . 'config/config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login Debug Test</h1>
        
        <?php
        // Test 1: Check functions
        echo '<div class="section">';
        echo '<h2>1. Required Functions</h2>';
        $functions = ['verifyPassword', 'executeQuery', 'fetchOne', 'loginUser', 'isLoggedIn'];
        foreach ($functions as $func) {
            if (function_exists($func)) {
                echo "<p style='color:green'>✅ $func() exists</p>";
            } else {
                echo "<p style='color:red'>❌ $func() NOT FOUND</p>";
            }
        }
        echo '</div>';
        
        // Test 2: Test database query
        echo '<div class="section">';
        echo '<h2>2. Database Query Test</h2>';
        if (function_exists('executeQuery') && function_exists('fetchOne')) {
            try {
                $testSql = "SELECT id, username, email, password FROM users LIMIT 1";
                $stmt = executeQuery($testSql);
                if ($stmt === false) {
                    echo "<p style='color:red'>❌ Query failed</p>";
                } else {
                    $testUser = fetchOne($stmt);
                    if ($testUser) {
                        echo "<p style='color:green'>✅ Query successful</p>";
                        echo "<p>Found user: " . htmlspecialchars($testUser['username'] ?? 'N/A') . "</p>";
                        echo "<p>Password hash exists: " . (isset($testUser['password']) && !empty($testUser['password']) ? 'Yes' : 'No') . "</p>";
                        if (isset($testUser['password'])) {
                            echo "<p>Password hash length: " . strlen($testUser['password']) . "</p>";
                            echo "<p>Password hash preview: " . htmlspecialchars(substr($testUser['password'], 0, 20)) . "...</p>";
                        }
                    } else {
                        echo "<p style='color:orange'>⚠️ No users found in database</p>";
                    }
                }
            } catch (Exception $e) {
                echo "<p style='color:red'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            } catch (Error $e) {
                echo "<p style='color:red'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        echo '</div>';
        
        // Test 3: Test verifyPassword
        echo '<div class="section">';
        echo '<h2>3. Password Verification Test</h2>';
        if (function_exists('verifyPassword')) {
            $testPassword = 'test123';
            $testHash = password_hash($testPassword, PASSWORD_DEFAULT);
            $result = verifyPassword($testPassword, $testHash);
            if ($result) {
                echo "<p style='color:green'>✅ verifyPassword() works correctly</p>";
            } else {
                echo "<p style='color:red'>❌ verifyPassword() failed</p>";
            }
        } else {
            echo "<p style='color:red'>❌ verifyPassword() function not found</p>";
        }
        echo '</div>';
        
        // Test 4: Test loginUser with sample
        echo '<div class="section">';
        echo '<h2>4. Login Function Test</h2>';
        if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
            $testUsername = $_POST['test_username'];
            $testPassword = $_POST['test_password'];
            
            echo "<p>Testing login with username: " . htmlspecialchars($testUsername) . "</p>";
            
            if (function_exists('loginUser')) {
                try {
                    $result = loginUser($testUsername, $testPassword);
                    echo "<pre>";
                    print_r($result);
                    echo "</pre>";
                    
                    if (isset($result['success']) && $result['success']) {
                        echo "<p style='color:green'>✅ Login successful!</p>";
                    } else {
                        echo "<p style='color:orange'>⚠️ Login failed: " . htmlspecialchars($result['message'] ?? 'Unknown error') . "</p>";
                    }
                } catch (Exception $e) {
                    echo "<p style='color:red'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                } catch (Error $e) {
                    echo "<p style='color:red'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
                    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
                }
            } else {
                echo "<p style='color:red'>❌ loginUser() function not found</p>";
            }
        } else {
            echo '<form method="POST">';
            echo '<p>Enter credentials to test:</p>';
            echo '<input type="text" name="test_username" placeholder="Username" style="padding: 8px; width: 200px; margin-right: 10px;">';
            echo '<input type="password" name="test_password" placeholder="Password" style="padding: 8px; width: 200px; margin-right: 10px;">';
            echo '<button type="submit" style="padding: 8px 15px;">Test Login</button>';
            echo '</form>';
        }
        echo '</div>';
        
        // Test 5: Check error logs
        echo '<div class="section">';
        echo '<h2>5. Recent Error Logs</h2>';
        $logFile = ABSPATH . 'logs/error.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $recentLines = array_slice($lines, -20); // Last 20 lines
            echo "<pre>" . htmlspecialchars(implode('', $recentLines)) . "</pre>";
        } else {
            echo "<p>Error log file not found: $logFile</p>";
        }
        echo '</div>';
        ?>
        
        <div class="section" style="background: #fff3cd; border-left-color: #ffc107;">
            <p><strong>⚠️ Security Note:</strong> Remove this file after debugging!</p>
        </div>
    </div>
</body>
</html>

