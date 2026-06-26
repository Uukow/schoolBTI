<?php
/**
 * Environment Configuration Test Script
 * 
 * Tests environment detection and configuration loading
 * 
 * Usage: Access via browser or run: php config/test-environment.php
 * 
 * @author School ERP Development Team
 * @version 1.0.0
 */

// Define ABSPATH
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');

// Load Environment Detector
if (file_exists(ABSPATH . 'includes/EnvironmentDetector.php')) {
    require_once ABSPATH . 'includes/EnvironmentDetector.php';
} else {
    die("Error: EnvironmentDetector.php not found!\n");
}

// Initialize
$env = EnvironmentDetector::getInstance();

// Output results
if (php_sapi_name() === 'cli') {
    // CLI output
    echo "========================================\n";
    echo "Environment Configuration Test\n";
    echo "========================================\n\n";
    echo "Detected Environment: " . $env->getEnvironment() . "\n";
    echo "Is Local: " . ($env->isLocal() ? 'Yes' : 'No') . "\n";
    echo "Is Production: " . ($env->isProduction() ? 'Yes' : 'No') . "\n";
    echo "Base URL: " . $env->getBaseUrl() . "\n\n";
    echo "Database Configuration:\n";
    echo "  Host: " . ($env->get('DB_HOST') ?? 'Not set') . "\n";
    echo "  User: " . ($env->get('DB_USER') ?? 'Not set') . "\n";
    echo "  Database: " . ($env->get('DB_NAME') ?? 'Not set') . "\n";
    echo "  Charset: " . ($env->get('DB_CHARSET') ?? 'Not set') . "\n\n";
    echo "Debug Mode: " . ($env->get('DEBUG_MODE') ? 'Enabled' : 'Disabled') . "\n";
    echo "Display Errors: " . ($env->get('DISPLAY_ERRORS') ? 'Enabled' : 'Disabled') . "\n";
    echo "Session Secure: " . ($env->get('SESSION_SECURE') ? 'Yes' : 'No') . "\n";
    echo "Force HTTPS: " . ($env->get('FORCE_HTTPS') ? 'Yes' : 'No') . "\n";
    echo "========================================\n";
} else {
    // Web output
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Environment Configuration Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
            .success { background: #d4edda; border-left-color: #28a745; }
            .warning { background: #fff3cd; border-left-color: #ffc107; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f8f9fa; font-weight: bold; }
            .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
            .badge-success { background: #28a745; color: white; }
            .badge-danger { background: #dc3545; color: white; }
            .badge-warning { background: #ffc107; color: #333; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Environment Configuration Test</h1>
            
            <div class="info <?php echo $env->isLocal() ? 'warning' : 'success'; ?>">
                <strong>Detected Environment:</strong> <?php echo strtoupper($env->getEnvironment()); ?><br>
                <strong>Base URL:</strong> <?php echo htmlspecialchars($env->getBaseUrl()); ?>
            </div>
            
            <h2>Environment Status</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Is Local</td>
                    <td><span class="badge <?php echo $env->isLocal() ? 'badge-warning' : 'badge-success'; ?>"><?php echo $env->isLocal() ? 'Yes' : 'No'; ?></span></td>
                </tr>
                <tr>
                    <td>Is Production</td>
                    <td><span class="badge <?php echo $env->isProduction() ? 'badge-success' : 'badge-warning'; ?>"><?php echo $env->isProduction() ? 'Yes' : 'No'; ?></span></td>
                </tr>
            </table>
            
            <h2>Database Configuration</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Host</td>
                    <td><?php echo htmlspecialchars($env->get('DB_HOST') ?? 'Not set'); ?></td>
                </tr>
                <tr>
                    <td>User</td>
                    <td><?php echo htmlspecialchars($env->get('DB_USER') ?? 'Not set'); ?></td>
                </tr>
                <tr>
                    <td>Database</td>
                    <td><?php echo htmlspecialchars($env->get('DB_NAME') ?? 'Not set'); ?></td>
                </tr>
                <tr>
                    <td>Charset</td>
                    <td><?php echo htmlspecialchars($env->get('DB_CHARSET') ?? 'Not set'); ?></td>
                </tr>
            </table>
            
            <h2>Security & Debug Settings</h2>
            <table>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Debug Mode</td>
                    <td><span class="badge <?php echo $env->get('DEBUG_MODE') ? 'badge-warning' : 'badge-success'; ?>"><?php echo $env->get('DEBUG_MODE') ? 'Enabled' : 'Disabled'; ?></span></td>
                </tr>
                <tr>
                    <td>Display Errors</td>
                    <td><span class="badge <?php echo $env->get('DISPLAY_ERRORS') ? 'badge-warning' : 'badge-success'; ?>"><?php echo $env->get('DISPLAY_ERRORS') ? 'Enabled' : 'Disabled'; ?></span></td>
                </tr>
                <tr>
                    <td>Session Secure</td>
                    <td><span class="badge <?php echo $env->get('SESSION_SECURE') ? 'badge-success' : 'badge-warning'; ?>"><?php echo $env->get('SESSION_SECURE') ? 'Yes' : 'No'; ?></span></td>
                </tr>
                <tr>
                    <td>Force HTTPS</td>
                    <td><span class="badge <?php echo $env->get('FORCE_HTTPS') ? 'badge-success' : 'badge-warning'; ?>"><?php echo $env->get('FORCE_HTTPS') ? 'Yes' : 'No'; ?></span></td>
                </tr>
            </table>
            
            <div class="info">
                <strong>Note:</strong> This test page should be removed or protected in production.
            </div>
        </div>
    </body>
    </html>
    <?php
}


