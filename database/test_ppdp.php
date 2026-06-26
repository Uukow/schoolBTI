<?php
require_once __DIR__ . '/../config/config.php';

$passed = 0;
$failed = 0;
function ok($label, $cond, $detail = '') {
    global $passed, $failed;
    if ($cond) { echo "[PASS] $label\n"; $passed++; }
    else { echo "[FAIL] $label" . ($detail ? " — $detail" : '') . "\n"; $failed++; }
}

echo "=== PPDP Module Tests ===\n\n";

$tables = ['hr_ppdp_programs', 'hr_ppdp_participants'];
foreach ($tables as $t) {
    global $conn;
    $row = fetchOne(executeQuery("SHOW TABLES LIKE '" . $conn->real_escape_string($t) . "'"));
    ok("Table $t", !empty($row));
}

$code = 'TEST-PPDP-' . time();
try {
    $stmt = executeQuery(
        "INSERT INTO hr_ppdp_programs (program_code, program_name, description, start_date, end_date, capacity, branch_id, facilitator_id, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'sssssiiis',
        [$code, 'Test Program', 'Desc', '2026-07-01', '2026-07-15', 10, null, null, 'Planned']
    );
    ok('Program INSERT', $stmt !== false);
    $prog = fetchOne(executeQuery("SELECT id FROM hr_ppdp_programs WHERE program_code = ?", 's', [$code]));
    $progId = (int)($prog['id'] ?? 0);
    ok('Program created', $progId > 0);

    $staff = fetchOne(executeQuery("SELECT id FROM staff LIMIT 1"));
    if ($staff) {
        $stmt2 = executeQuery(
            "INSERT INTO hr_ppdp_participants (program_id, staff_id, registration_date, status) VALUES (?, ?, CURDATE(), 'Registered')",
            'ii', [$progId, (int)$staff['id']]
        );
        ok('Participant INSERT', $stmt2 !== false);
    }

    $stmt3 = executeQuery(
        "UPDATE hr_ppdp_programs SET program_name=?, description=?, start_date=?, end_date=?, capacity=?, branch_id=?, facilitator_id=?, status=? WHERE id=?",
        'ssssiiisi',
        ['Updated', 'D2', '2026-07-01', '2026-07-20', 15, null, null, 'Open', $progId]
    );
    ok('Program UPDATE bind', $stmt3 !== false);

    executeQuery("DELETE FROM hr_ppdp_programs WHERE id = ?", 'i', [$progId]);
    ok('Cleanup', true);
} catch (Throwable $e) {
    ok('PPDP workflow', false, $e->getMessage());
}

$files = ['ajax/hr/get-ppdp-programs.php', 'ajax/hr/save-ppdp-program.php', 'ajax/hr/get-ppdp-participants.php', 'ajax/hr/save-ppdp-participant.php', 'modules/hr/ppdp-programs.php'];
foreach ($files as $f) {
    $out = []; $code = 0;
    exec('php -l ' . escapeshellarg(ABSPATH . $f) . ' 2>&1', $out, $code);
    ok("Syntax $f", $code === 0, implode(' ', $out));
}

echo "\n=== Results: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
