<?php
/**
 * Test Login Page Load
 * 
 * Tests if login.php can load without errors
 * Remove after fixing
 */

// Define ABSPATH
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

// Enable error display for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Login Page Components</h1>";

// Test 1: Load config
echo "<h2>1. Loading config.php...</h2>";
try {
    ob_start();
    require_once ABSPATH . 'config/config.php';
    $output = ob_get_clean();
    if (empty($output)) {
        echo "<p style='color:green'>✅ config.php loaded successfully</p>";
        echo "<p>APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</p>";
        echo "<p>DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "</p>";
    } else {
        echo "<p style='color:orange'>⚠️ config.php produced output:</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<p style='color:red'>❌ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

// Test 2: Check database connection
echo "<h2>2. Testing database connection...</h2>";
if (function_exists('getDBConnection')) {
    try {
        $conn = getDBConnection();
        if ($conn === false) {
            echo "<p style='color:orange'>⚠️ Database connection returned false (will use defaults)</p>";
        } else {
            echo "<p style='color:green'>✅ Database connection successful</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:orange'>⚠️ Database connection exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Error $e) {
        echo "<p style='color:red'>❌ Database connection fatal error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ getDBConnection function not found</p>";
}

// Test 3: Check required functions
echo "<h2>3. Checking required functions...</h2>";
$requiredFunctions = ['isLoggedIn', 'redirect', 'sanitize', 'loginUser', 'getCurrentUser'];
foreach ($requiredFunctions as $func) {
    if (function_exists($func)) {
        echo "<p style='color:green'>✅ $func() exists</p>";
    } else {
        echo "<p style='color:red'>❌ $func() NOT FOUND</p>";
    }
}

// Test 4: Check SettingsManager
echo "<h2>4. Testing SettingsManager...</h2>";
if (class_exists('SettingsManager')) {
    try {
        $settings = SettingsManager::getInstance();
        echo "<p style='color:green'>✅ SettingsManager instantiated</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ SettingsManager exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Error $e) {
        echo "<p style='color:red'>❌ SettingsManager fatal error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ SettingsManager class not found</p>";
}

// Test 5: Try to simulate login.php
echo "<h2>5. Simulating login.php execution...</h2>";
try {
    // This is what login.php does
    if (function_exists('isLoggedIn')) {
        $loggedIn = isLoggedIn();
        echo "<p>isLoggedIn() returned: " . ($loggedIn ? 'true' : 'false') . "</p>";
    }
    echo "<p style='color:green'>✅ Login page simulation successful</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Exception during simulation: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Error $e) {
    echo "<p style='color:red'>❌ Fatal error during simulation: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}

echo "<hr><p><strong>⚠️ Remove this file after fixing issues!</strong></p>";

