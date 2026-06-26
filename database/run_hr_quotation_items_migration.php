<?php
defined('ABSPATH') or define('ABSPATH', dirname(__DIR__) . '/');
require_once ABSPATH . 'config/config.php';
if (!SYSTEM_INSTALLED) die("Not installed.\n");

$file = ABSPATH . 'database/migrations/hr/007_hr_quotation_items.sql';
$sql = preg_replace('/--.*$/m', '', file_get_contents($file));
$conn = getDBConnection();
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if (empty($stmt)) continue;
    if (!$conn->query($stmt) && !preg_match('/already exists|Duplicate/i', $conn->error)) {
        echo "[ERROR] {$conn->error}\n";
    } else {
        echo "[OK]\n";
    }
}
echo "Quotation items migration done.\n";
