<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Attendance Corrections Tests ===\n\n";

$row = fetchOne(executeQuery("SHOW TABLES LIKE 'hr_attendance_corrections'"));
ok('Table hr_attendance_corrections', !empty($row));

$staff = fetchOne(executeQuery("SELECT id FROM staff WHERE status = 'Active' LIMIT 1"));
$staffId = (int)($staff['id'] ?? 0);
$user = fetchOne(executeQuery("SELECT id FROM users WHERE is_active = 1 LIMIT 1"));
$userId = (int)($user['id'] ?? 1);
ok('Test staff/user', $staffId > 0 && $userId > 0);

$date = date('Y-m-d', strtotime('-5 days'));
try {
    $stmt = executeQuery(
        "INSERT INTO hr_attendance_corrections
         (staff_id, attendance_date, attendance_id, requested_check_in, requested_check_out,
          requested_status, reason, status, submitted_by)
         VALUES (?, ?, NULL, '08:30:00', '17:00:00', 'Present', 'Test correction', 'Submitted', ?)",
        'isi',
        [$staffId, $date, $userId]
    );
    ok('INSERT bind', $stmt !== false);

    $rec = fetchOne(executeQuery(
        "SELECT id FROM hr_attendance_corrections WHERE staff_id = ? AND attendance_date = ? AND reason = 'Test correction'",
        'is',
        [$staffId, $date]
    ));
    $cid = (int)($rec['id'] ?? 0);
    ok('Correction created', $cid > 0);

    $stmt2 = executeQuery(
        "UPDATE hr_attendance_corrections SET status='Manager_Approved', manager_approved_by=?, manager_approved_at=NOW() WHERE id=?",
        'ii',
        [$userId, $cid]
    );
    ok('Manager approve UPDATE', $stmt2 !== false);

    $stmt3 = executeQuery(
        "UPDATE hr_attendance_corrections SET status='HR_Approved', hr_approved_by=?, hr_approved_at=NOW() WHERE id=?",
        'ii',
        [$userId, $cid]
    );
    ok('HR approve UPDATE', $stmt3 !== false);

    executeQuery("DELETE FROM hr_attendance_corrections WHERE id = ?", 'i', [$cid]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('Workflow', false, $e->getMessage());
}

$files = [
    'ajax/hr/attendance-corrections.php',
    'modules/hr/attendance-corrections.php',
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
