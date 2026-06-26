<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0; $failed = 0;
function ok($l, $c, $d = '') {
    global $passed, $failed;
    if ($c) { echo "[PASS] $l\n"; $passed++; }
    else { echo "[FAIL] $l" . ($d ? " — $d" : '') . "\n"; $failed++; }
}

echo "=== Grievance Module Tests ===\n\n";
global $conn;

foreach (['hr_grievances', 'hr_grievance_actions'] as $t) {
    $row = fetchOne(executeQuery("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'"));
    ok("Table $t", !empty($row));
}

$code = 'TEST-GRV-' . time();
try {
    $stmt = executeQuery(
        "INSERT INTO hr_grievances (grievance_no, staff_id, is_anonymous, category, subject, description, priority, status)
         VALUES (?, NULL, 1, 'Other', 'Test', 'Description text', 'Medium', 'Submitted')",
        's', [$code]
    );
    ok('Grievance INSERT', $stmt !== false);
    $g = fetchOne(executeQuery("SELECT id FROM hr_grievances WHERE grievance_no = ?", 's', [$code]));
    $gid = (int)($g['id'] ?? 0);
    ok('Grievance created', $gid > 0);

    $stmt2 = executeQuery(
        "INSERT INTO hr_grievance_actions (grievance_id, action_by, action_type, comment) VALUES (?, 1, 'Submitted', 'Test')",
        'i', [$gid]
    );
    ok('Action INSERT', $stmt2 !== false);

    $stmt3 = executeQuery(
        "UPDATE hr_grievances SET status=?, resolution=?, resolved_at=IF(? IN ('Resolved','Closed'), NOW(), NULL) WHERE id=?",
        'sssi', ['Resolved', 'Done', 'Resolved', $gid]
    );
    ok('Status UPDATE bind', $stmt3 !== false);

    executeQuery("DELETE FROM hr_grievances WHERE id = ?", 'i', [$gid]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('Workflow', false, $e->getMessage());
}

$files = [
    'ajax/hr/get-grievances.php', 'ajax/hr/save-grievance.php',
    'ajax/hr/get-grievance-actions.php', 'modules/hr/grievances.php'
];
foreach ($files as $f) {
    $out = []; $ec = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $ec);
    ok("Syntax $f", $ec === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
