<?php
/**
 * Diagnostic Script for Production Issues
 * 
 * Helps identify configuration and environment issues
 * Remove this file after fixing issues
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define ABSPATH
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

// Set error reporting for diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>System Diagnostics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #e9ecef; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>System Diagnostics</h1>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        // Check PHP Version
        echo '<div class="section">';
        echo '<h2>PHP Version</h2>';
        $phpVersion = phpversion();
        echo '<p>PHP Version: <strong>' . $phpVersion . '</strong></p>';
        if (version_compare($phpVersion, '7.0.0', '<')) {
            $errors[] = 'PHP 7.0 or higher is required';
            echo '<p class="error">❌ PHP 7.0 or higher is required</p>';
        } else {
            $success[] = 'PHP version is compatible';
            echo '<p class="success">✅ PHP version is compatible</p>';
        }
        echo '</div>';
        
        // Check ABSPATH
        echo '<div class="section">';
        echo '<h2>Paths</h2>';
        echo '<p>ABSPATH: <code>' . ABSPATH . '</code></p>';
        echo '<p>ABSPATH exists: ' . (file_exists(ABSPATH) ? '✅ Yes' : '❌ No') . '</p>';
        if (!file_exists(ABSPATH)) {
            $errors[] = 'ABSPATH does not exist';
        }
        echo '</div>';
        
        // Check Required Files
        echo '<div class="section">';
        echo '<h2>Required Files</h2>';
        $requiredFiles = [
            'includes/EnvironmentDetector.php',
            'config/environments/local.php',
            'config/environments/production.php',
            'config/database.php',
        ];
        
        echo '<table>';
        echo '<tr><th>File</th><th>Status</th></tr>';
        foreach ($requiredFiles as $file) {
            $path = ABSPATH . $file;
            $exists = file_exists($path);
            echo '<tr>';
            echo '<td>' . $file . '</td>';
            if ($exists) {
                echo '<td><span class="badge badge-success">✅ Exists</span></td>';
                $success[] = "File exists: $file";
            } else {
                echo '<td><span class="badge badge-danger">❌ Missing</span></td>';
                $errors[] = "Missing file: $file";
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        
        // Check EnvironmentDetector
        echo '<div class="section">';
        echo '<h2>EnvironmentDetector</h2>';
        if (file_exists(ABSPATH . 'includes/EnvironmentDetector.php')) {
            try {
                require_once ABSPATH . 'includes/EnvironmentDetector.php';
                if (class_exists('EnvironmentDetector')) {
                    echo '<p class="success">✅ EnvironmentDetector class loaded</p>';
                    try {
                        $env = EnvironmentDetector::getInstance();
                        echo '<p>Environment: <strong>' . $env->getEnvironment() . '</strong></p>';
                        echo '<p>Is Local: ' . ($env->isLocal() ? 'Yes' : 'No') . '</p>';
                        echo '<p>Is Production: ' . ($env->isProduction() ? 'Yes' : 'No') . '</p>';
                        echo '<p>Base URL: <code>' . htmlspecialchars($env->getBaseUrl()) . '</code></p>';
                        $success[] = 'EnvironmentDetector working';
                    } catch (Exception $e) {
                        echo '<p class="error">❌ Error instantiating EnvironmentDetector: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        $errors[] = 'EnvironmentDetector instantiation failed: ' . $e->getMessage();
                    } catch (Error $e) {
                        echo '<p class="error">❌ Fatal error with EnvironmentDetector: ' . htmlspecialchars($e->getMessage()) . '</p>';
                        $errors[] = 'EnvironmentDetector fatal error: ' . $e->getMessage();
                    }
                } else {
                    echo '<p class="error">❌ EnvironmentDetector class not found after require</p>';
                    $errors[] = 'EnvironmentDetector class not found';
                }
            } catch (Exception $e) {
                echo '<p class="error">❌ Error loading EnvironmentDetector: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $errors[] = 'Error loading EnvironmentDetector: ' . $e->getMessage();
            } catch (Error $e) {
                echo '<p class="error">❌ Fatal error loading EnvironmentDetector: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $errors[] = 'Fatal error loading EnvironmentDetector: ' . $e->getMessage();
            }
        } else {
            echo '<p class="error">❌ EnvironmentDetector.php file not found</p>';
            $errors[] = 'EnvironmentDetector.php missing';
        }
        echo '</div>';
        
        // Check Environment Config Files
        echo '<div class="section">';
        echo '<h2>Environment Configuration Files</h2>';
        $envFiles = ['local.php', 'production.php'];
        foreach ($envFiles as $envFile) {
            $path = ABSPATH . 'config/environments/' . $envFile;
            if (file_exists($path)) {
                try {
                    $config = require $path;
                    if (is_array($config)) {
                        echo '<p class="success">✅ ' . $envFile . ' - Valid array (' . count($config) . ' settings)</p>';
                    } else {
                        echo '<p class="error">❌ ' . $envFile . ' - Not returning array</p>';
                        $errors[] = "$envFile does not return array";
                    }
                } catch (Exception $e) {
                    echo '<p class="error">❌ ' . $envFile . ' - Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    $errors[] = "Error loading $envFile: " . $e->getMessage();
                } catch (Error $e) {
                    echo '<p class="error">❌ ' . $envFile . ' - Fatal error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    $errors[] = "Fatal error in $envFile: " . $e->getMessage();
                }
            } else {
                echo '<p class="warning">⚠️ ' . $envFile . ' - Not found (will use defaults)</p>';
                $warnings[] = "$envFile not found";
            }
        }
        echo '</div>';
        
        // Check Server Variables
        echo '<div class="section">';
        echo '<h2>Server Information</h2>';
        echo '<table>';
        echo '<tr><th>Variable</th><th>Value</th></tr>';
        $serverVars = ['HTTP_HOST', 'SERVER_NAME', 'SERVER_ADDR', 'HTTPS', 'REQUEST_URI', 'SCRIPT_NAME'];
        foreach ($serverVars as $var) {
            $value = $_SERVER[$var] ?? 'Not set';
            echo '<tr><td>' . $var . '</td><td><code>' . htmlspecialchars($value) . '</code></td></tr>';
        }
        echo '</table>';
        echo '</div>';
        
        // Check Directories
        echo '<div class="section">';
        echo '<h2>Directory Permissions</h2>';
        $dirs = ['logs', 'uploads', 'config/environments'];
        echo '<table>';
        echo '<tr><th>Directory</th><th>Exists</th><th>Writable</th></tr>';
        foreach ($dirs as $dir) {
            $path = ABSPATH . $dir;
            $exists = file_exists($path);
            $writable = $exists && is_writable($path);
            echo '<tr>';
            echo '<td>' . $dir . '</td>';
            echo '<td>' . ($exists ? '✅' : '❌') . '</td>';
            echo '<td>' . ($writable ? '✅' : '❌') . '</td>';
            echo '</tr>';
            if (!$exists) {
                $warnings[] = "Directory missing: $dir";
            }
            if ($exists && !$writable) {
                $warnings[] = "Directory not writable: $dir";
            }
        }
        echo '</table>';
        echo '</div>';
        
        // Summary
        echo '<div class="section ' . (count($errors) > 0 ? 'error' : (count($warnings) > 0 ? 'warning' : 'success')) . '">';
        echo '<h2>Summary</h2>';
        echo '<p><strong>Errors:</strong> ' . count($errors) . '</p>';
        echo '<p><strong>Warnings:</strong> ' . count($warnings) . '</p>';
        echo '<p><strong>Success:</strong> ' . count($success) . '</p>';
        
        if (count($errors) > 0) {
            echo '<h3>Errors:</h3><ul>';
            foreach ($errors as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
        }
        
        if (count($warnings) > 0) {
            echo '<h3>Warnings:</h3><ul>';
            foreach ($warnings as $warning) {
                echo '<li>' . htmlspecialchars($warning) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // Try loading main config
        echo '<div class="section">';
        echo '<h2>Main Config Test</h2>';
        try {
            ob_start();
            require_once ABSPATH . 'config/config.php';
            $output = ob_get_clean();
            if (empty($output)) {
                echo '<p class="success">✅ config.php loaded without errors</p>';
                if (defined('APP_URL')) {
                    echo '<p>APP_URL: <code>' . APP_URL . '</code></p>';
                }
                if (defined('DB_HOST')) {
                    echo '<p>DB_HOST: <code>' . DB_HOST . '</code></p>';
                }
            } else {
                echo '<p class="warning">⚠️ config.php produced output:</p>';
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Error loading config.php: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } catch (Error $e) {
            echo '<p class="error">❌ Fatal error loading config.php: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getFile() . ':' . $e->getLine()) . '</pre>';
        }
        echo '</div>';
        ?>
        
        <div class="section warning">
            <p><strong>⚠️ Security Note:</strong> Remove this diagnostic file after fixing issues!</p>
        </div>
    </div>
</body>
</html>

