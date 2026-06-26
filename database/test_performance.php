<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Performance Review Module Tests ===\n\n";
global $conn;

$row = fetchOne(executeQuery("SHOW TABLES LIKE 'hr_performance_reviews'"));
ok('Table hr_performance_reviews', !empty($row));

$cols = ['kpis', 'goals', 'strengths', 'improvements'];
foreach ($cols as $col) {
    $c = fetchOne(executeQuery(
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hr_performance_reviews' AND COLUMN_NAME = ?",
        's', [$col]
    ));
    ok("Column $col", !empty($c));
}

$staff = fetchOne(executeQuery("SELECT id FROM staff WHERE status = 'Active' LIMIT 1"));
$staffId = (int)($staff['id'] ?? 0);
ok('Active staff for test', $staffId > 0);

$user = fetchOne(executeQuery("SELECT id FROM users WHERE is_active = 1 LIMIT 1"));
$reviewerId = (int)($user['id'] ?? 1);

$kpis = json_encode([['name' => 'Test KPI', 'score' => 4.5]]);
$period = 'TEST-' . time();

try {
    $stmt = executeQuery(
        "INSERT INTO hr_performance_reviews (staff_id, reviewer_id, review_period, rating, comments, goals, strengths, improvements, kpis, status, review_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'iisdsssssss',
        [$staffId, $reviewerId, $period, 4.5, 'Test comment', 'Goal A', 'Strong', 'Improve', $kpis, 'Draft', date('Y-m-d')]
    );
    ok('INSERT bind (iisdsssssss)', $stmt !== false);

    $rec = fetchOne(executeQuery("SELECT id, kpis FROM hr_performance_reviews WHERE review_period = ?", 's', [$period]));
    $rid = (int)($rec['id'] ?? 0);
    ok('Review created', $rid > 0);

    $decoded = json_decode($rec['kpis'] ?? '', true);
    ok('KPI JSON stored', is_array($decoded) && ($decoded[0]['name'] ?? '') === 'Test KPI');

    $stmt2 = executeQuery(
        "UPDATE hr_performance_reviews SET staff_id=?, reviewer_id=?, review_period=?, rating=?, comments=?,
         goals=?, strengths=?, improvements=?, kpis=?, status=?, review_date=? WHERE id=?",
        'iisdsssssssi',
        [$staffId, $reviewerId, $period, 4.8, 'Updated', 'G2', 'S2', 'I2', $kpis, 'Submitted', date('Y-m-d'), $rid]
    );
    ok('UPDATE bind (iisdsssssssi)', $stmt2 !== false);

    $stats = fetchOne(executeQuery(
        "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) AS draft FROM hr_performance_reviews"
    ));
    ok('Stats query', isset($stats['total']));

    executeQuery("DELETE FROM hr_performance_reviews WHERE id = ?", 'i', [$rid]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('Workflow', false, $e->getMessage());
}

$files = [
    'ajax/hr/get-performance-reviews.php',
    'ajax/hr/save-performance-review.php',
    'modules/hr/performance.php',
    'database/migrations/hr/008_hr_performance_kpis.sql'
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
