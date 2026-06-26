<?php
/**
 * Run HR Phase 2 & 3 Migrations
 */
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');
require_once ABSPATH . 'config/config.php';

if (!SYSTEM_INSTALLED) {
    die("Error: System is not installed.\n");
}

$migrations = [
    ABSPATH . 'database/migrations/hr/002_phase2_payroll_hr_admin.sql',
    ABSPATH . 'database/migrations/hr/003_phase3_operations.sql',
];

echo "========================================\n";
echo "HR Phase 2 & 3 Migrations\n";
echo "========================================\n\n";

$conn = getDBConnection();
if (!$conn) die("DB connection failed.\n");

$totalOk = 0;
$totalSkip = 0;
$totalErr = 0;

foreach ($migrations as $migrationFile) {
    echo "Running: " . basename($migrationFile) . "\n";
    if (!file_exists($migrationFile)) {
        echo "  [SKIP] File not found\n";
        continue;
    }

    $sql = preg_replace('/--.*$/m', '', file_get_contents($migrationFile));
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $i => $statement) {
        if (empty($statement)) continue;
        try {
            if ($conn->query($statement)) {
                $totalOk++;
            } else {
                $err = $conn->error;
                if (preg_match('/Duplicate column|already exists|Duplicate key name/i', $err)) {
                    $totalSkip++;
                } else {
                    $totalErr++;
                    echo "  [ERROR] #" . ($i+1) . ": $err\n";
                }
            }
        } catch (Exception $e) {
            if (preg_match('/Duplicate column|already exists/i', $e->getMessage())) {
                $totalSkip++;
            } else {
                $totalErr++;
                echo "  [ERROR] #" . ($i+1) . ": " . $e->getMessage() . "\n";
            }
        }
    }
    echo "  Done.\n\n";
}

echo "OK: $totalOk | Skipped: $totalSkip | Errors: $totalErr\n";
if ($totalErr === 0) {
    echo "\nMigrations completed. Visit: " . APP_URL . "modules/hr/dashboard.php\n";
}
