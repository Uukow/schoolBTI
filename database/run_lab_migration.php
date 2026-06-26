<?php
/**
 * LAB Management System - Database Migration Runner
 * Run this file once via browser or CLI to create all lab tables
 */

define('ABSPATH', dirname(__DIR__) . '/');
require_once ABSPATH . 'config/config.php';

if (!isLoggedIn() || !hasRole(['Super Admin'])) {
    die('Access denied. Super Admin only.');
}

$sql = file_get_contents(__DIR__ . '/lab_management_migration.sql');

// Split on semicolons but keep multi-line statements intact
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => !empty($s) && !preg_match('/^--/', ltrim($s))
);

$success = 0;
$errors  = [];

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    $result = executeQuery($statement);
    if ($result === false) {
        $errors[] = substr($statement, 0, 120) . '...';
    } else {
        $success++;
    }
}

echo '<h2>LAB Migration Complete</h2>';
echo "<p>Executed: <strong>$success</strong> statements</p>";
if ($errors) {
    echo '<p>Errors (' . count($errors) . '):</p><ul>';
    foreach ($errors as $e) {
        echo '<li>' . htmlspecialchars($e) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p style="color:green">All statements executed successfully.</p>';
}
echo '<p><a href="' . APP_URL . 'modules/laboratory/dashboard.php">Go to LAB Dashboard &rarr;</a></p>';
