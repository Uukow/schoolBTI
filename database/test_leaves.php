<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Leave Module Tests ===\n\n";

$row = fetchOne(executeQuery("SHOW TABLES LIKE 'leave_applications'"));
ok('Table leave_applications', !empty($row));

$staff = fetchOne(executeQuery("SELECT id FROM staff WHERE status = 'Active' LIMIT 1"));
$staffId = (int)($staff['id'] ?? 0);
$lt = fetchOne(executeQuery("SELECT id FROM leave_types LIMIT 1"));
$leaveTypeId = (int)($lt['id'] ?? 0);
ok('Test data (staff + leave type)', $staffId > 0 && $leaveTypeId > 0);

$period = 'TEST-LV-' . time();
try {
    $stmt = executeQuery(
        "INSERT INTO leave_applications (staff_id, leave_type_id, start_date, end_date, total_days, reason, status, approval_stage)
         VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending')",
        'iissds',
        [$staffId, $leaveTypeId, date('Y-m-d'), date('Y-m-d', strtotime('+1 day')), 2, 'Test leave ' . $period]
    );
    ok('INSERT bind (iissds)', $stmt !== false);

    $rec = fetchOne(executeQuery(
        "SELECT id FROM leave_applications WHERE reason LIKE ? ORDER BY id DESC LIMIT 1",
        's',
        ['%TEST-LV-%']
    ));
    $lid = (int)($rec['id'] ?? 0);
    ok('Application created', $lid > 0);

    $stmt2 = executeQuery(
        "UPDATE leave_applications SET approval_stage='Manager_Approved', status='Pending',
         manager_approved_by=1, manager_approval_date=NOW() WHERE id=?",
        'i',
        [$lid]
    );
    ok('Manager approve UPDATE', $stmt2 !== false);

    $stmt3 = executeQuery(
        "UPDATE leave_applications SET status='Cancelled', approval_stage='Cancelled' WHERE id=?",
        'i',
        [$lid]
    );
    ok('Cancel UPDATE', $stmt3 !== false);

    executeQuery("DELETE FROM leave_applications WHERE id = ?", 'i', [$lid]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('Workflow', false, $e->getMessage());
}

if ($staffId && $leaveTypeId && class_exists('LeaveBalanceService')) {
    $bal = LeaveBalanceService::getRemainingDays($staffId, $leaveTypeId);
    ok('LeaveBalanceService::getRemainingDays', is_numeric($bal));
}

$files = [
    'ajax/hr/get-leave-applications.php',
    'ajax/hr/apply-leave.php',
    'ajax/hr/update-leave-status.php',
    'ajax/hr/get-leave-balances.php',
    'modules/hr/leaves.php',
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
