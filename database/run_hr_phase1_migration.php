<?php
/**
 * Run HR Phase 1 Foundation Migration
 *
 * @author TacliinHub Development Team
 */

defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');
require_once ABSPATH . 'config/config.php';

if (!SYSTEM_INSTALLED) {
    die("Error: System is not installed. Please run setup.php first.\n");
}

echo "========================================\n";
echo "HR Phase 1 Foundation Migration\n";
echo "========================================\n\n";

$migrationFile = ABSPATH . 'database/migrations/hr/001_phase1_foundation.sql';

if (!file_exists($migrationFile)) {
    die("Error: Migration file not found: {$migrationFile}\n");
}

$sql = file_get_contents($migrationFile);
$sql = preg_replace('/--.*$/m', '', $sql);

$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function ($stmt) {
        return !empty($stmt) && strlen(trim($stmt)) > 0;
    }
);

echo "Found " . count($statements) . " SQL statements.\n\n";

$conn = getDBConnection();
if (!$conn) {
    die("Error: Could not connect to database.\n");
}

$successCount = 0;
$errorCount = 0;
$skipCount = 0;

foreach ($statements as $index => $statement) {
    $statement = trim($statement);
    if (empty($statement)) {
        continue;
    }

    if (stripos($statement, 'CREATE INDEX IF NOT EXISTS') !== false) {
        if (preg_match('/CREATE INDEX IF NOT EXISTS `?(\w+)`? ON `?(\w+)`?/i', $statement, $matches)) {
            $indexName = $matches[1];
            $tableName = $matches[2];
            $checkSql = "SELECT COUNT(*) as count FROM information_schema.statistics
                         WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('ss', $tableName, $indexName);
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($result['count'] > 0) {
                echo "  [SKIP] Index {$indexName} on {$tableName}\n";
                $skipCount++;
                continue;
            }
            $statement = str_ireplace('CREATE INDEX IF NOT EXISTS', 'CREATE INDEX', $statement);
        }
    }

    try {
        if ($conn->query($statement)) {
            $successCount++;
            echo "  [OK] Statement " . ($index + 1) . "\n";
        } else {
            $err = $conn->error;
            if (stripos($err, 'Duplicate column') !== false
                || stripos($err, 'already exists') !== false
                || stripos($err, 'Duplicate key name') !== false) {
                echo "  [SKIP] Statement " . ($index + 1) . ": {$err}\n";
                $skipCount++;
            } else {
                $errorCount++;
                echo "  [ERROR] Statement " . ($index + 1) . ": {$err}\n";
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'Duplicate column') !== false || stripos($msg, 'already exists') !== false) {
            echo "  [SKIP] Statement " . ($index + 1) . ": {$msg}\n";
            $skipCount++;
        } else {
            $errorCount++;
            echo "  [ERROR] Statement " . ($index + 1) . ": {$msg}\n";
        }
    }
}

echo "\n========================================\n";
echo "Successful: {$successCount} | Skipped: {$skipCount} | Errors: {$errorCount}\n";

if ($errorCount === 0) {
    echo "\nHR Phase 1 migration completed.\n";
    echo "Next: " . APP_URL . "modules/hr/dashboard.php\n";
}

echo "\n";
